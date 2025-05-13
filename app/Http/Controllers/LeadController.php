<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;
use App\Services\LeadService;
use Illuminate\Support\Facades\DB;

class LeadController extends Controller
{
    protected $leadService;

    public function __construct(LeadService $leadService)
    {
        $this->leadService = $leadService;
    }

    /**
     * Get all leads.
     *
     * Retrieves a list of all leads in the system, sorted by the latest created.
     *
     * @group Leads
     * @response {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "household": "Example",
     *       "citizenship": "Singapore Citizen",
     *       "requirement": "Yes",
     *       "household_income": "3000",
     *       "ownership_status": "No, do not own any HDB",
     *       "private_property_ownership": "No",
     *       "first_time_applicant": "Yes",
     *       "name": "John Doe",
     *       "email": "johndoe@example.com",
     *       "phone_number": "123456789",
     *       "verified_at": "2025-05-01T07:00:00",
     *       "send_discord": false,
     *       "created_at": "2025-05-01T07:00:00",
     *       "updated_at": "2025-05-01T07:00:00",
     *       "deleted_at": null
     *     }
     *   ]
     * }
     */
    public function index()
    {
        $leads = Lead::latest()->get();

        return response()->json([
            'success' => true,
            'data' => $leads
        ]);
    }

    /**
     * Create a new lead.
     *
     * Stores a new lead in the database.
     *
     * @group Leads
     * @bodyParam household string required The household type of the lead. Example: Example Household
     * @bodyParam citizenship string required The citizenship status. Example: Singapore Citizen
     * @bodyParam requirement string required The requirement status. Example: Yes
     * @bodyParam household_income string required The household income. Example: 3000
     * @bodyParam ownership_status string required The ownership status. Example: No, do not own any HDB
     * @bodyParam private_property_ownership string required The private property ownership status. Example: No
     * @bodyParam first_time_applicant string required First time applicant status. Example: Yes
     * @bodyParam name string required The name of the lead. Example: John Doe
     * @bodyParam email string required The email address of the lead. Example: johndoe@example.com
     * @bodyParam phone_number string required The phone number of the lead. Example: 123456789
     * @response {
     *   "status": "success",
     *   "message": "Form submitted successfully",
     *   "data": {
     *     "lead_id": 1,
     *     "result": "congratulation"
     *   },
     *   "listing": {
     *     "result": "singmap-congratulation"
     *   }
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "household": [
     *       "The household field is required."
     *     ]
     *   }
     * }
     */
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
                'name' => $validated['name'],
                'phone_number' => $validated['phone_number'],
                'email' => $validated['email'],
            ]);

            if ($lead) {
                $this->leadService->sendLeadToDiscord($lead->toArray());
            }

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
                    $response['data']['listing'] = 'singmap-appeal-mop';
                    break;
                case $lead->ownership_status === 'Yes, MOP completed':
                    $response['data']['result'] = 'congratulation';
                    $response['data']['listing'] = 'singmap-congratulation';
                    break;
                case $lead->ownership_status === 'Yes, still within MOP':
                    $response['data']['result'] = 'mop';
                    $response['data']['listing'] = 'singmap-appeal-mop';
                    break;
                case $lead->ownership_status === 'No, do not own any HDB':
                    $response['data']['result'] = 'appeal';
                    $response['data']['listing'] = 'singmap-appeal-mop';
                    break;
                default:
                    $response['data']['result'] = 'disqualification';
                    $response['data']['listing'] = 'singmap-appeal-mop';
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

    /**
     * Get a single lead.
     *
     * Retrieves details of the lead with the specified ID.
     *
     * @group Leads
     * @urlParam id integer required The ID of the lead. Example: 1
     * @response {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "household": "Example",
     *     "citizenship": "Singapore Citizen",
     *     "requirement": "Yes",
     *     "household_income": "3000",
     *     "ownership_status": "No, do not own any HDB",
     *     "private_property_ownership": "No",
     *     "first_time_applicant": "Yes",
     *     "name": "John Doe",
     *     "email": "johndoe@example.com",
     *     "phone_number": "123456789",
     *     "verified_at": "2025-05-01T07:00:00",
     *     "send_discord": false,
     *     "created_at": "2025-05-01T07:00:00",
     *     "updated_at": "2025-05-01T07:00:00",
     *     "deleted_at": null
     *   }
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Lead not found."
     * }
     */
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
