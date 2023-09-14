<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\APIBaseController as BaseController;
use App\Models\User;
use App\Models\ZohoAccess;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ClientException;
use Carbon\Carbon,Log,Exception,DB;
use Validator;

class APIRegisterController extends BaseController
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request) {
        $rules = [
            'name'          => 'required|max:250',
            'email'         => 'required|email|max:250|unique:users,email',
            'password'      => 'required',
            'c_password'    => 'required|same:password',
            'phone'         => 'nullable|max:20',
            'type'          => 'required|max:20|in:individual,business',
            'business_name' => 'nullable|max:100',
            'closest_metro' => 'nullable|max:100',
            'bio_desc'      => 'nullable|max:500',
        ];

        $validator = Validator::make($request->all(), $rules);   
        if($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors()->first(), 400);       
        }

        if($request->input('type',null) == 'business' && $request->input('business_name',null) == null) {
            return $this->sendError('Validation Error.', "Business name field is required.", 400);  
        }
        if($request->input('business_name',null) != null && ($request->input('type',null) != 'business' || $request->input('type',null) == null)) {
            return $this->sendError('Validation Error.', "Type field is required and must match.", 400);  
        }
   
        DB::beginTransaction();
        try {
            $input = $request->all();
            $input['password'] = bcrypt($input['password']);
            $input['phone'] = $request->input('phone',null);
            $input['type'] = $request->input('type',null);
            $input['business_name'] = $request->input('business_name',null);
            $input['closest_metro'] = $request->input('closest_metro',null);
            $input['bio_desc'] = $request->input('bio_desc',null);

            $user = User::create($input);

            if(isset($user->id)) {
                $method = 'POST';

                $this->baseUri = 'https://www.zohoapis.com/crm/v3/App_Customers';
                $token         = ZohoAccess::orderBy('id','desc')->value('access_token'); //$response->access_token;
                $this->secret = 'Bearer '.$token;

                $jayParsedAry = [
                    "data" => [
                        [
                            'Signup_Via'                    => "iApp",
                            'Name'                          => $user->name,
                            'First_Name'                    => explode(' ',$user->name)[0],
                            'Email'                         => $user->email,
                            'Active'                        => true,
                            'Customer_Type'                 => ucwords($input['type']),
                            'Business_Name'                 => ($input['type']=='business') ? $input['business_name'] : '',
                            'Phone'                         => $user->phone,
                            'Nearby_Metro_Name'             => $user->closest_metro,
                            'Stripe_Customer_ID'            => '',
                            'Stripe_Subscription_ID'        => '',
                            'Profile_Photo'                 => '', //asset('storage/users/'.$user->image),
                            'Your_Bio_Company_Description'  => $user->bio_desc
                        ]
                    ],
                    "trigger" => [
                        "approval", 
                        "workflow", 
                        "blueprint" 
                    ] 
                ];
                $call     = $this->request($method,$jayParsedAry);
                $response = $this->successResponse($call);
                $response = json_decode($response->getContent());
                $array    = json_decode(json_encode($response), true);
                
                User::where('id',$user->id)->update(['zoho_crm_contact_id'=>$array['data'][0]['details']['id']]);

            }

            DB::commit();
            $user = User::find($user->id);
            //$success['token'] = $user->createToken('MyApp')->plainTextToken;
            $success['name'] = $user->name;
            $success['email'] = $user->email;
            $success['zoho_id'] = $user->zoho_crm_contact_id;
            $success['created_at'] = $user->created_at;
            return $this->sendResponse($success, 'User registered successfully.');
        } catch(ClientException $e) {
            DB::rollback();
            $request = Psr7\Message::toString($e->getRequest());
            $response = Psr7\Message::toString($e->getResponse());

            \Log::error("Request: ".$request.", Response: ".$response);
            return $this->sendError('Warning.', 'Something wrong with Zoho Connection, please try after some time.', 400);
        } catch(\Throwable $e) {
            \Log::error($e->getMessage());
            return $this->sendError('Error.', $e->getMessage(), 400);
        }
    }
   
    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request) {
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user(); 
            $success['token'] = $user->createToken('MyApp')->plainTextToken; 
            $success['name'] = $user->name;
            $success['email'] =  $user->email;
            $success['created_at'] =  $user->created_at;
   
            return $this->sendResponse($success, 'User login successfully.');
        } else {
            return $this->sendError('Unauthorised.','Login credentials are not correct');
        } 
    }
}
