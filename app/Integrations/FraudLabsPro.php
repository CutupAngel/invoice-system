<?php

namespace App\Integrations;

use Validator;
use Settings;

class FraudLabsPro extends Integration
{
    const TITLE = 'FraudLabs Pro';
    const SHORTNAME = 'fraudlabspro';
    const DESCRIPTION = "FraudLabs Pro - Report and Query Unpleasant Clients, Fraudsters, Scammers, Spammers, Abusers on FraudLabs Pro.";

    public static function getInfo()
    {
        return [
            'title' => self::TITLE,
            'shortname' => self::SHORTNAME,
            'description' => self::DESCRIPTION,
            'status' => self::checkEnabled()
        ];
    }

    public static function checkEnabled()
    {
        return Settings::get('integration.fraudlabs') === '1' ? true : false;
    }

    public static function toggle()
    {
        if (self::checkEnabled()) {
            Settings::set([
                'integration.fraudlabs' => false
            ]);
            return 0;
        }
        else
        {
            Settings::set([
                'integration.fraudlabs' => true
            ]);
            return 1;
        }
    }

    public function getError()
    {
        // TODO: Implement getError() method.
    }

    public static function getSetupForm()
    {
        return view('Integrations.fraudLabsPro');
    }

    public static function setup(\Illuminate\Http\Request $request)
    {
        $v = Validator::make($request->all(), [
    			'riskScore' => 'numeric|min:0|max:100',
        ]);

        if ($v->fails())
        {
            return redirect()->back()->withErrors($v->errors());
        }

        $setting = [];
        if($request->has('fraudlabs')) $setting['integration.fraudlabs'] = true;
        else $setting['integration.fraudlabs'] = false;

        $setting['fraudlabs.apiKey'] = $request->apiKey;
        $setting['fraudlabs.riskScore'] = $request->riskScore;

        if($request->has('rejectFreeEmail')) $setting['fraudlabs.rejectFreeEmail'] = true;
        else $setting['fraudlabs.rejectFreeEmail'] = false;

        if($request->has('rejectCountryMismatch')) $setting['fraudlabs.rejectCountryMismatch'] = true;
        else $setting['fraudlabs.rejectCountryMismatch'] = false;

        if($request->has('rejectAnonymousNetworks')) $setting['fraudlabs.rejectAnonymousNetworks'] = true;
        else $setting['fraudlabs.rejectAnonymousNetworks'] = false;

        if($request->has('rejectHighRiskCountry')) $setting['fraudlabs.rejectHighRiskCountry'] = true;
        else $setting['fraudlabs.rejectHighRiskCountry'] = false;

        if($request->has('skipCheckExisting')) $setting['fraudlabs.skipCheckExisting'] = true;
        else $setting['fraudlabs.skipCheckExisting'] = false;

        Settings::set($setting);

        $successStatus = trans('backend.settings-fraudlabs-success');
        return back()->with('status', $successStatus);
    }
}
