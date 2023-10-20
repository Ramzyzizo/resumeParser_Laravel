<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\Resume;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class ResumeController extends Controller
{

    public function get_user_data(Request $request)
    {
        $token = $request->token;
        $email = $request->email;
        $user = User::where('email', $email)->first();
        if($user){
            try {
                $auth_user = JWTAuth::setToken($token)->toUser();
                $resume = $auth_user->profile;
                $resume['name']=$auth_user->name;
                $resume['skills']=$auth_user->skills;
                return response()->json($resume);
            }catch (\Exception $e) {
                return response()->json(['errors'=>'unauthorized'],401);
            }
        }else{
            return response()->json(['errors'=>'unauthorized'],401);
        }
    }

    public function update(Request $request)
    {
        $token = $request->data['token'];
        $email = $request->data['email'];
        $user = User::where('email', '=',$email)->first();
        if($user){
            try {
                $auth_user = JWTAuth::setToken($token)->toUser();
                $user->profile->update([
                    'adress'=>$request->formValues['address'],
                    'phone'=>$request->formValues['phone'],
                    'birthdate'=>$request->formValues['birthdate'],
                    'marital_status'=>$request->formValues['marital'],
                    'military_status'=>$request->formValues['military'],
                    'education'=>$request->formValues['education'],
                    'education_date'=>$request->formValues['education_date']
                ]);
                $user->update(['name'=>$request->formValues['name']]);
                foreach ($request->formValues['skills'] as $skill){
                    Skill::where('id',$skill['id'])->update([
                        'name'=>$skill['name'],
                        'level'=>$skill['level'],
                    ]);
                }
                return response()->json(['response'=>$email]);
            }catch (\Exception $e) {
                return response()->json(['errors'=>'Something is swrong'],422);
            }

        }else{
            return response()->json(['errors'=>'unauthorized']);
        }

    }


}
