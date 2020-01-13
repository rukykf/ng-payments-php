<?php

namespace Kofi\NgPayments\Tests\functional\Rave;

use Kofi\NgPayments\Exceptions\InvalidRequestBodyException;
use Kofi\NgPayments\PaymentProviders\PaymentProviderFactory;
use Kofi\NgPayments\Plan;
use PHPUnit\Framework\TestCase;

class PlanTest extends TestCase
{
    protected function setUp()
    {
        PaymentProviderFactory::setPaymentProviderConfig(['provider' => 'flutterwave']);
    }

    public function testPlanConstructorOverload()
    {
        $attributes = ["name" => "plan_name", "naira_amount" => 3000, "interval" => "weekly"];

        $plan = new Plan($attributes);
        $this->assertEquals($attributes, $plan->getAttributes());
    }

    public function testCreatePlan()
    {
        $plan = new Plan("Test Plan", 3000, "weekly");
        $result = $plan->save();
        $this->assertNotNull($result);

        $this->expectException(InvalidRequestBodyException::class);
        $plan = new Plan();
        $plan->setName('Failed Test Plan');
        $plan->save();
    }

    public function testUpdatePlan()
    {
        $plan = new Plan("Test Plan", 3000, "weekly");
        $plan_id = $plan->save();
        $this->assertNotNull($plan_id);

        $fetched_plan = Plan::fetch($plan_id);
        $this->assertEquals("weekly", $fetched_plan->interval);
        $this->assertEquals("Test Plan", $fetched_plan->name);

        $plan->name = "Edited Test Plan";
        $plan->save();

        $fetched_plan = Plan::fetch($plan_id);
        $this->assertEquals("Edited Test Plan", $fetched_plan->name);

        $plan = new Plan();
        $plan->plan_code = "UNKNOWN_CODE";
        $this->assertNull($plan->save());

    }

    public function testFetchPlan()
    {
        $plan = new Plan("Test Plan", 3000, "weekly");
        $plan_id = $plan->save();
        $fetched_plan = Plan::fetch($plan_id);
        $this->assertEquals('3000', $fetched_plan->amount);
        $this->assertEquals('Test Plan', $fetched_plan->name);

        $fetched_plan = Plan::fetch("Invalid Plan");
        $this->assertNull($fetched_plan);
    }

    public function testFetchAllPlans()
    {
        //to ensure there's at least one plan in the list
        $plan = new Plan("Test Plan", 3000, "weekly");
        $plan->save();

        $plans = Plan::fetchAll();
        $this->assertNotNull($plans);
        $this->assertContainsOnlyInstancesOf(Plan::class, $plans);
    }

    public function testDeletePlan()
    {
        $plan = new Plan("Test Plan", 3000, "weekly");
        $plan_id = $plan->save();

        $result = Plan::delete($plan_id);
        $this->assertEquals('cancelled', $result);

        $result = Plan::delete("InvalidId");
        $this->assertNull($result);
    }


}
