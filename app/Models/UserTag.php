<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTag extends Model
{
    use HasFactory;

    const TAG_USER_MINISTRY = '/MINISTRY';
    const TAG_ADMIN_PERMISSION_ROLE_ID = '/ADMIN/PERMISSION_ROLE_ID';
    const TAG_ADMIN_NICKNAME = '/ADMIN/NICKNAME';

    const TAG_LAST_VISIT_DATE = '/DATE/LAST_VISIT';
    const TAG_COUNT_VISIT = '/COUNT/VISIT';

    const TAG_CHURCH_ROLE = '/CHURCH_ROLE/ID';

    const TAG_ATTEND_CHURCH_DAY = '/ATTEND/CHURCH/DAY';

    //主日事奉
    const MINISTRY_PIANO = '/MINISTRY/SERVICE/PIANO';
    const MINISTRY_POETRY = '/MINISTRY/SERVICE/POETRY';
    const MINISTRY_FLOWER = '/MINISTRY/SERVICE/FLOWER';
    const MINISTRY_ENTERTAIN = '/MINISTRY/SERVICE/ENTERTAIN';
    const MINISTRY_COOK = '/MINISTRY/SERVICE/COOK';
    const MINISTRY_ITEM_MANAGEMENT = '/MINISTRY/SERVICE/ITEM_MANAGEMENT';
    const MINISTRY_SUNDAY_TEACHER = '/MINISTRY/SERVICE/SUNDAY_TEACHER';
    const MINISTRY_CHILD_CARE = '/MINISTRY/SERVICE/CHILD_CARE';

    //肢體關懷
    const MINISTRY_DISTRIBUTE_WEEKLY_REPORTS = '/MINISTRY/CARE/DISTRIBUTE_WEEKLY_REPORTS';
    const MINISTRY_VISIT = '/MINISTRY/CARE/VISIT';
    const MINISTRY_INTERCESSION = '/MINISTRY/CARE/INTERCESSION';
    const MINISTRY_CONNECTION = '/MINISTRY/CARE/CONNECTION';

    //行政事工
    const MINISTRY_DATA_ARRANGEMENT = '/MINISTRY/ADMINISTRATIVE/DATA_ARRANGEMENT';
    const MINISTRY_DOCUMENT_PROCESSING = '/MINISTRY/ADMINISTRATIVE/DOCUMENT_PROCESSING';
    const MINISTRY_COMPUTER_MAINTENANCE = '/MINISTRY/ADMINISTRATIVE/COMPUTER_MAINTENANCE';
    const MINISTRY_ENVIRONMENTAL_MAINTENANCE = '/MINISTRY/ADMINISTRATIVE/ENVIRONMENTAL_MAINTENANCE';
    const MINISTRY_AUDIO_CONTROL = '/MINISTRY/ADMINISTRATIVE/AUDIO_CONTROL';
    const MINISTRY_PROJECTION = '/MINISTRY/ADMINISTRATIVE/PROJECTION';

    //社區事工
    const MINISTRY_SPEECH = '/MINISTRY/COMMUNITY/SPEECH';
    const MINISTRY_TALENT = '/MINISTRY/COMMUNITY/TALENT';
    const MINISTRY_WEEKEND_KID_CAMP = '/MINISTRY/COMMUNITY/WEEKEND_KID_CAMP';
    const MINISTRY_COMMUNITY_CARE = '/MINISTRY/COMMUNITY/COMMUNITY_CARE';

    //團契事奉
    const MINISTRY_SUNDAY_SERVICE = '/MINISTRY/FELLOWSHIP/SUNDAY_SERVICE';
    const MINISTRY_SUNDAY_SCHOOL = '/MINISTRY/FELLOWSHIP/SUNDAY_SCHOOL';
    const MINISTRY_BIBLE_STUDY = '/MINISTRY/FELLOWSHIP/BIBLE_STUDY';
    const MINISTRY_TEEN_FELLOWSHIP = '/MINISTRY/FELLOWSHIP/TEEN_FELLOWSHIP';
    const MINISTRY_YOUTH_FELLOWSHIP = '/MINISTRY/FELLOWSHIP/YOUTH_FELLOWSHIP';
    const MINISTRY_COUPLE_FELLOWSHIP = '/MINISTRY/FELLOWSHIP/COUPLE_FELLOWSHIP';
    const MINISTRY_WOMEN_FELLOWSHIP = '/MINISTRY/FELLOWSHIP/WOMEN_FELLOWSHIP';
    const MINISTRY_ELDER_FELLOWSHIP = '/MINISTRY/FELLOWSHIP/ELDER_FELLOWSHIP';
    const MINISTRY_CHOIR = '/MINISTRY/FELLOWSHIP/CHOIR';

    public static function getMinistriesArray(): array
    {
        return [
            self::MINISTRY_PIANO,
            self::MINISTRY_POETRY,
            self::MINISTRY_FLOWER,
            self::MINISTRY_ENTERTAIN,
            self::MINISTRY_COOK,
            self::MINISTRY_ITEM_MANAGEMENT,
            self::MINISTRY_SUNDAY_TEACHER,
            self::MINISTRY_CHILD_CARE,
            self::MINISTRY_DISTRIBUTE_WEEKLY_REPORTS,
            self::MINISTRY_VISIT,
            self::MINISTRY_INTERCESSION,
            self::MINISTRY_CONNECTION,
            self::MINISTRY_DATA_ARRANGEMENT,
            self::MINISTRY_DOCUMENT_PROCESSING,
            self::MINISTRY_COMPUTER_MAINTENANCE,
            self::MINISTRY_ENVIRONMENTAL_MAINTENANCE,
            self::MINISTRY_AUDIO_CONTROL,
            self::MINISTRY_PROJECTION,
            self::MINISTRY_SPEECH,
            self::MINISTRY_TALENT,
            self::MINISTRY_WEEKEND_KID_CAMP,
            self::MINISTRY_COMMUNITY_CARE,
            self::MINISTRY_SUNDAY_SERVICE,
            self::MINISTRY_SUNDAY_SCHOOL,
            self::MINISTRY_BIBLE_STUDY,
            self::MINISTRY_TEEN_FELLOWSHIP,
            self::MINISTRY_YOUTH_FELLOWSHIP,
            self::MINISTRY_COUPLE_FELLOWSHIP,
            self::MINISTRY_WOMEN_FELLOWSHIP,
            self::MINISTRY_ELDER_FELLOWSHIP,
            self::MINISTRY_CHOIR,
        ];
    }

    public static function renewLastVisitDateTag($userId)
    {
        $lastVisitDateTag = self::query()
            ->where('user_id', $userId)
            ->where('tag_key', self::TAG_LAST_VISIT_DATE)
            ->first();

        if ($lastVisitDateTag == null) {
            $lastVisitDateTag = new self();
            $lastVisitDateTag['user_id'] = $userId;
            $lastVisitDateTag['tag_key'] = self::TAG_LAST_VISIT_DATE;
        }
        $visitDate = Visit::query()
            ->where('target_user_id', $userId)
            ->where('status', Visit::STATUS_DONE)
            ->orderByDesc('visit_date')
            ->pluck('visit_date')
            ->first();

        $lastVisitDateTag['value'] = $visitDate;
        $lastVisitDateTag->save();

        return $lastVisitDateTag;
    }

    public static function renewCountVisitTag($userId)
    {
        $countVisitTag = self::query()
            ->where('user_id', $userId)
            ->where('tag_key', self::TAG_COUNT_VISIT)
            ->first();

        if ($countVisitTag == null) {
            $countVisitTag = new self();
            $countVisitTag['user_id'] = $userId;
            $countVisitTag['tag_key'] = self::TAG_COUNT_VISIT;
        }

        $count = Visit::query()
            ->where('target_user_id', $userId)
            ->where('status', Visit::STATUS_DONE)
            ->count();

        $countVisitTag['value'] = $count;
        $countVisitTag->save();

        return $countVisitTag;
    }

    public static function createByLegacyUserTags(int $userId, $legacyUserTags)
    {
        foreach ($legacyUserTags as $legacyUserTag) {
            $userTag = new self();
            $userTag['user_id'] = $userId;
            $userTag['tag_key'] = $legacyUserTag['tag_key'];
            $userTag['value'] = $legacyUserTag['value'];
            $userTag->save();
        }
    }

    public static function mapOrCreateUserTag(?int $legacyUserId, int $newUserId, int $defaultRoleId)
    {
        $legacyUserTags = UserTag::query()
            ->where('user_id', $legacyUserId)
            ->get();

        if (count($legacyUserTags) == 0) {
            $roleTag = new self();
            $roleTag['user_id'] = $newUserId;
            $roleTag['tag_key'] = self::TAG_CHURCH_ROLE;
            $roleTag['value'] = $defaultRoleId;
            $roleTag->save();
        } else {
            self::createByLegacyUserTags($newUserId, $legacyUserTags);
        }
    }
}
