<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Imports\DedicationImport;
use App\Managers\FileManager;
use App\Models\AccountTitle;
use App\Models\Dedication;
use App\Models\UserProfile;
use App\Models\UserStatisticalTag;
use App\Utils\DateTimeUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class DedicationController extends Controller
{
    const ACCEPTABLE_EXTENSIONS = ['csv', 'xls', 'xlsx'];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $dedications = Dedication::query()
            ->with('accountTitle');

        if ($request->has('user_id')) {
            $userProfile = UserProfile::query()
                ->where('user_id', $request['user_id'])
                ->first();

            $dedications->where('identify_id', $userProfile['identify_id']);
        }

        if ($request->has('receipt_numbers')) {
            $dedications->where(function ($query) use ($request) {
                foreach ($request['receipt_numbers'] as $receiptNumber) {
                    $query->orWhere('receipt_number', 'like', '%' . $receiptNumber . '%');
                }
            });
        }

        if ($request->has('names')) {
            $dedications->where(function ($query) use ($request) {
                foreach ($request['names'] as $name) {
                    $query->orWhere('name', 'like', '%' . $name . '%');
                }
            });
        }

        if ($request->has('account_title_ids')) {
            $dedications->whereIn('account_title_id', $request['account_title_ids']);
        }

        if ($request->has('identify_ids')) {
            $dedications->where(function ($query) use ($request) {
                foreach ($request['identify_ids'] as $identifyId) {
                    $query->orWhere('identify_id', 'like', '%' . $identifyId . '%');
                }
            });
        }

        if ($request->has(['from', 'to'])) {
            $dedications->where('dedicate_date', '>=', $request['from'])
                ->where('dedicate_date', '<=', $request['to']);
        }

        if ($request['summary'] === 'true') {
            return response()->json([
                'amount' => number_format($dedications->sum('amount')),
            ]);
        }

        if ($request->has('sorted_by')) {
            $direction = $request['direction'] ? $request['direction'] : 'asc';
            $dedications->orderBy($request['sorted_by'], $direction);
        } else {
            $dedications->orderByDesc('id');
        }

        // return response()->json($dedications->paginate());
        return response()->json($dedications->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $file = $request->file('file');

        if (!collect(self::ACCEPTABLE_EXTENSIONS)->contains($file->extension())) {
            abort(422, 'Invalid file extension');
        }

        $file->store('dedications');
        $excel = Excel::toArray(new DedicationImport(), FileManager::convertToUTF8($file));

        $errors = [];
        $successfulCount = 0;

        foreach (array_reverse($excel[0]) as $value) {
            try {
                if (empty($value[4])) {
                    throw new \Exception('Empty Identify ID');
                }

                $accountTitleId = AccountTitle::getIdFromSerialNumber($value[7]);
                $amount = str_replace(',', '', $value[6]);
                $dedication = new Dedication();
                $dedication['name'] = $value[3];
                $dedication['identify_id'] = $value[4] ?? '';
                $dedication['account_title_id'] = $accountTitleId;
                $dedication['amount'] = $amount;
                $dedication['receipt_number'] = $value[1];
                $dedication['dedicate_date'] = DateTimeUtil::parseRepublicEra($value[0])->format('Y-m-d');
                $dedication['created_by_user_id'] = $user['id'];
                $dedication['method'] = Dedication::METHOD_IMPORT;
                $dedication['file_name'] = $file->hashName();
                $dedication->save();

                UserStatisticalTag::updateOrCreateAmountTag($value[4], $accountTitleId, $amount);
                UserStatisticalTag::updateOrCreateCountTag($value[4], $accountTitleId);
                $successfulCount++;
            } catch (\Exception $exception) {
                Log::error($exception->getMessage());
                $errors[] = [
                    'name' => $value[3],
                    'identify_id' => $value[4],
                    'account_title_serial_number' => $value[7],
                    'account_title' => $value[8],
                    'amount' => $value[6],
                    'receipt_number' => $value[1],
                    'dedicate_date' => $value[1],
                    'message' => $exception->getMessage(),
                ];
            }
        }

        return response()->json([
            'successful_count' => $successfulCount,
            'import_invalid_data' => $errors,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $dedication = Dedication::query()
            ->where('id', $id)
            ->with('accountTitle')
            ->firstOrFail();

        return response()->json($dedication);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $dedication = Dedication::query()
            ->where('id', $id)
            ->first();

        $identifyId = $dedication['identify_id'];
        $accountTitleId = $dedication['account_title_id'];

        $amount = str_replace(',', '', $dedication['amount']);
        UserStatisticalTag::updateOrCreateAmountTag($identifyId, $accountTitleId, $amount * -1);
        UserStatisticalTag::updateOrCreateCountTag($identifyId, $accountTitleId, false);

        $dedication->forceDelete();

        return response(null);
    }

    public function dedicationFormatCheck(Request $request)
    {
        $file = $request->file('file');

        if (!collect(self::ACCEPTABLE_EXTENSIONS)->contains($file->extension())) {
            abort(422, 'Invalid file extension');
        }

        $excel = Excel::toArray(new DedicationImport(), FileManager::convertToUTF8($file));

        $errors = [];
        $successfulCount = 0;

        $count = count($excel[0]);

        $receiptNumbers = Dedication::query()
            ->pluck('receipt_number');
        foreach ($excel[0] as $value) {
            // remove summary row
            if ($count - 1 == $successfulCount + count($errors)) {
                break;
            }

            try {
                AccountTitle::getIdFromSerialNumber($value[7]);

                if (empty($value[4])) {
                    throw new \Exception('身分證字號為空');
                }

                if ($receiptNumbers->contains($value[1])) {
                    throw new \Exception('收據編號重複');
                }

                $successfulCount++;
            } catch (\Exception $exception) {
                Log::error($exception->getMessage());
                $errors[] = [
                    'name' => $value[3],
                    'identify_id' => $value[4],
                    'account_title_serial_number' => $value[7],
                    'account_title' => $value[8],
                    'amount' => $value[6],
                    'receipt_number' => $value[1],
                    'dedicate_date' => $value[0],
                    'message' => $exception->getMessage(),
                ];
            }
        }

        return response()->json([
            'successful_count' => $successfulCount,
            'import_invalid_data' => $errors,
        ]);
    }
}
