<?php

namespace Kofi\NgPayments\Tests\functional\Paystack;

use Kofi\NgPayments\Exceptions\FeatureNotSupportedException;
use Kofi\NgPayments\Exceptions\InvalidRequestBodyException;
use Kofi\NgPayments\Plan;
use PHPUnit\Framework\TestCase;

class PlanTest extends TestCase
{
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
        $plan_code = $plan->save();
        $this->assertNotNull($plan_code);

        $fetched_plan = Plan::fetch($plan_code);
        $this->assertEquals(true, $fetched_plan->send_sms);
        $this->assertEquals(true, $fetched_plan->send_invoices);

        $plan->send_sms = false;
        $plan->send_invoices = false;
        $plan->save();

        $fetched_plan = Plan::fetch($plan_code);
        $this->assertEquals(false, $fetched_plan->send_sms);
        $this->assertEquals(false, $fetched_plan->send_invoices);

        $plan = new Plan();
        $plan->plan_code = "UNKNOWN_CODE";
        $this->assertNull($plan->save());

    }

    public function testFetchPlan()
    {
        $plan = new Plan("Test Plan", 3000, "weekly");
        $plan_code = $plan->save();
        $fetched_plan = Plan::fetch($plan_code);
        $this->assertEquals('300000', $fetched_plan->amount);
        $this->assertEquals('Test Plan', $fetched_plan->name);

        $fetched_plan = Plan::fetch("Invalid Plan");
        $this->assertNull($fetched_plan);

    }

    public function testFetchAllPlans()
    {
        //to ensure there's at least one plan in the list
        $plan = new Plan("Test Plan", 3000, "weekly");
        $plan_code = $plan->save();

        $plans = Plan::fetchAll();
        $this->assertNotNull($plans);
        $this->assertContainsOnlyInstancesOf(Plan::class, $plans);
    }

    public function testDeletePlan()
    {
        $plan = new Plan("Test Plan", 3000, "weekly");
        $plan_code = $plan->save();
        $this->expectException(FeatureNotSupportedException::class);
        Plan::delete($plan_code);
    }


}
