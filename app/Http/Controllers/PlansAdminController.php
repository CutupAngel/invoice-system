<?php

namespace App\Http\Controllers;

use App\Plan;
use App\Plan_Cycle;

use Auth;
use Permissions;
use Storage;
use Illuminate\Http\Request;

class PlansAdminController extends Controller
{
    public function getPlans()
    {
        $plans = Plan::get();

        return view('Plans.planList', ['plans' => $plans]);
    }

    public function getPlan($planId)
    {
        $type = ($planId === 'new' ? 'Create' : 'Edit');
        if ($planId === 'new') {
            $plan = new Plan();
        } else {
            $plan = Plan::findOrFail($planId);
        }

        return view('Plans.planForm', ['type' => $type, 'plan' => $plan]);
    }

    public function savePlan(Request $request, $planId = 'new')
    {
        $this->validate($request, [
            'name' => 'required'
        ]);

        $errors = [];

        if ($planId !== 'new') {
            $plan = Plan::findOrFail($planId);
        } else {
            $plan = new Plan();
        }

        $plan->name = $request->input('name');
        $plan->description = $request->input('description');
        $plan->tax = $request->input('vat');
        $plan->clients = $request->input('clients');
        $plan->invoices = $request->input('invoices');
        $plan->staff = $request->input('staff');
        $plan->trial = $request->input('trial');
        $plan->save();

        $cycles = $plan->cycles;
        $savedCycles = [];
        foreach ($request->input('cycle.id') as $i => $id) {
            if ($id == 'new') {
                $cycle = new Plan_Cycle();
            } else {
                if ($cycles->contains('id', $id)) {
                    $cycle = Plan_Cycle::findOrFail($id);
                } else {
                    continue;
                }
            }

            $cycle->plan_id = $plan->id;
            $cycle->price = $request->input('cycle.price')[$i];
            $cycle->fee = $request->input('cycle.setup')[$i] ?: 0.00;
            $cycle->cycle = $request->input('cycle.cycle')[$i];
            $cycle->save();

            $savedCycles[] = $cycle->id;
        }

        foreach ($cycles as $cycle) {
            if (in_array($cycle->id, $savedCycles)) {
                continue;
            }

            $cycle->delete();
        }

        return redirect('/plans')->with(['success' => 'Plan has been saved successfully!'])->withErrors($errors);
    }

    public function delete($packageId)
    {
        $delete = Plan::findOrFail($planId);
        $delete->delete();

        return redirect('/orders')->with(['success' => '{$delete->name} was successfully deleted.']);
    }
}
