<?php

namespace Tests\Unit\Manager;

use App\Managers\IntercessionManager;
use App\Models\Intercession;
use Tests\TestCase;

class IntercessionManagerTest extends TestCase
{
    public function testIntercessionType()
    {
        $this->assertTrue(IntercessionManager::isPersonalType(Intercession::GENERAL_CARD));
        $this->assertTrue(IntercessionManager::isPersonalType(Intercession::PRIVATE_CARD));
        $this->assertTrue(IntercessionManager::isPersonalType(Intercession::EMERGENCY_CARD));
        $this->assertNotTrue(IntercessionManager::isPersonalType(Intercession::MINISTRY_CARD));
        $this->assertNotTrue(IntercessionManager::isPersonalType(Intercession::THANKFUL_CARD));
        $this->assertNotTrue(IntercessionManager::isPersonalType('card_type'));

        $this->assertNotTrue(IntercessionManager::isMinistryType(Intercession::GENERAL_CARD));
        $this->assertNotTrue(IntercessionManager::isMinistryType(Intercession::PRIVATE_CARD));
        $this->assertNotTrue(IntercessionManager::isMinistryType(Intercession::EMERGENCY_CARD));
        $this->assertTrue(IntercessionManager::isMinistryType(Intercession::MINISTRY_CARD));
        $this->assertNotTrue(IntercessionManager::isMinistryType(Intercession::THANKFUL_CARD));
        $this->assertNotTrue(IntercessionManager::isMinistryType('card_type'));

        $this->assertNotTrue(IntercessionManager::isThankfulType(Intercession::GENERAL_CARD));
        $this->assertNotTrue(IntercessionManager::isThankfulType(Intercession::PRIVATE_CARD));
        $this->assertNotTrue(IntercessionManager::isThankfulType(Intercession::EMERGENCY_CARD));
        $this->assertNotTrue(IntercessionManager::isThankfulType(Intercession::MINISTRY_CARD));
        $this->assertTrue(IntercessionManager::isThankfulType(Intercession::THANKFUL_CARD));
        $this->assertNotTrue(IntercessionManager::isThankfulType('card_type'));
    }

    public function testNewCardId()
    {
        $this->assertEquals('代22020', IntercessionManager::newCardId('代22019', Intercession::GENERAL_CARD));
        $this->assertEquals('急22020', IntercessionManager::newCardId('急22019', Intercession::EMERGENCY_CARD));
        $this->assertEquals('私22020', IntercessionManager::newCardId('私22019', Intercession::PRIVATE_CARD));
        $this->assertEquals('事22020', IntercessionManager::newCardId('事22019', Intercession::MINISTRY_CARD));
        $this->assertEquals('謝22020', IntercessionManager::newCardId('謝22019', Intercession::THANKFUL_CARD));

        $this->assertEquals('代221000', IntercessionManager::newCardId('代22999', Intercession::GENERAL_CARD));

        $this->assertEquals('代23001', IntercessionManager::newCardId('代22090', Intercession::GENERAL_CARD, '23'));

        $this->assertEquals('代22001', IntercessionManager::newCardId('', Intercession::GENERAL_CARD));
        $this->assertEquals('代22001', IntercessionManager::newCardId(null, Intercession::GENERAL_CARD));

        try {
            IntercessionManager::newCardId('代22019', 'unknownType');
        } catch (\Exception $exception) {
            $this->assertEquals('When new card id get unknown card type: unknownType', $exception->getMessage());
        }

        try {
            IntercessionManager::newCardId('Error Input String', Intercession::GENERAL_CARD);
        } catch (\Exception $exception) {
            $this->assertEquals('When new card id get error latest card id: Error Input String', $exception->getMessage());
        }
    }

    public function testCardIdShouldReset()
    {
        $this->assertTrue(IntercessionManager::isCardIdShouldReset('代21020', '22'));
        $this->assertFalse(IntercessionManager::isCardIdShouldReset('代22020', '22'));

        $this->assertTrue(IntercessionManager::isCardIdShouldReset('', '22'));
        $this->assertTrue(IntercessionManager::isCardIdShouldReset(null, '22'));
        $this->assertTrue(IntercessionManager::isCardIdShouldReset('代22020', ''));
        $this->assertTrue(IntercessionManager::isCardIdShouldReset('代22020', 'ERROR'));
    }
}
