<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeadController extends Controller
{
    public function index()
    {
        $leads = Lead::latest()->get();

        return response()->json([
            'success' => true,
            'data' => $leads
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'household' => 'required|string',
            'citizenship' => 'required|string',
            'requirement' => 'required|string',
            'household_income' => 'required|string',
            'ownership_status' => 'required|string',
            'private_property_ownership' => 'required|string',
            'first_time_applicant' => 'required|string',
            'name' => 'required|string',
            'phone_number' => 'required|string',
            'email' => 'required|email',
        ]);

        try {
            $lead = Lead::create([
                'household' => $validated['household'],
                'citizenship' => $validated['citizenship'],
                'requirement' => $validated['requirement'],
                'household_income' => $validated['household_income'],
                'ownership_status' => $validated['ownership_status'],
                'private_property_ownership' => $validated['private_property_ownership'],
                'first_time_applicant' => $validated['first_time_applicant'],
                'phone_number' => $validated['phone_number'],
                'email' => $validated['email'],
            ]);

            $response = [
                'status' => 'success',
                'message' => 'Form submitted successfully',
                'data' => [
                    'lead_id' => $lead->id,
                ],
            ];

            switch (true) {
                case $lead->citizenship === 'No, not Singapore Citizens or Permanent Residents' ||
                    $lead->requirement === 'No' ||
                    $lead->household_income === 'No' ||
                    $lead->private_property_ownership === 'Yes':
                    $response['data']['result'] = 'disqualification';
                    $response['listing']['result'] = 'singmap-appeal-mop';
                    break;
                case $lead->ownership_status === 'Yes, MOP completed':
                    $response['data']['result'] = 'congratulation';
                    $response['listing']['result'] = 'singmap-congratulation';
                    break;
                case $lead->ownership_status === 'Yes, still within MOP':
                    $response['data']['result'] = 'mop';
                    $response['listing']['result'] = 'singmap-appeal-mop';
                    break;
                case $lead->ownership_status === 'No, do not own any HDB':
                    $response['data']['result'] = 'appeal';
                    $response['listing']['result'] = 'singmap-appeal-mop';
                    break;
                default:
                    $response['data']['result'] = 'disqualification';
                    $response['listing']['result'] = 'singmap-appeal-mop';
                    break;
            }

            return response()->json($response);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Database Insert Failed',
                'errorInfo' => $e->getMessage()
            ]);
        }
    }

    public function show($id)
    {
        try {
            $lead = Lead::find($id);

            return response()->json([
                'success' => true,
                'data' => $lead
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lead not found.'
            ], 404);
        }
    }
}
