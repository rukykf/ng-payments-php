<?php

namespace Metav\NgPayments\Tests\Functional;

use Metav\NgPayments\Exceptions\InvalidRequestBodyException;
use Metav\NgPayments\Plan;
use PHPUnit\Framework\TestCase;

class PlanTest extends TestCase
{
    public function testPlanConstructorOverload()
    {
        $attributes = ["color" => "blue", "shade" => "grey"];

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

    public function testFetchPlan()
    {
        $plan = new Plan("Test Plan", 3000, "weekly");
        $plan_id = $plan->save();
        $fetched_plan = Plan::fetch($plan_id);
        $this->assertEquals('300000', $fetched_plan->amount);
        $this->assertEquals('Test Plan', $fetched_plan->name);
    }

    public function testListPlan()
    {
        //to ensure there's at least one plan in the least
        $plan = new Plan("Test Plan", 3000, "weekly");
        $plan_id = $plan->save();

        $plans = Plan::list();
        $this->assertNotNull($plans);
        $this->assertContainsOnlyInstancesOf(Plan::class, $plans);
    }


}
