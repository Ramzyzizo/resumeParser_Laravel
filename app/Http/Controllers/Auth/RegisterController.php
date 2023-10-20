<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\Resume;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use GuzzleHttp\Client;
use Spatie\PdfToText\Pdf;


class RegisterController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|unique:users',
            'name' => 'required|string|min:5',
            'password' => 'required|string|min:5|confirmed',
            'CV' => 'required|mimes:pdf|max:10000'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        } else {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);
            $credentials = $request->only('email', 'password');
            $token = JWTAuth::attempt($credentials);

            $fileName = 'CV' . date('s-m-h') . $request->file('CV')->getClientOriginalName();
            $path = public_path() . '/' . 'Files/';
            $request->file('CV')->move($path, $fileName);
            $resume = Resume::create(['user_id' => $user->id, 'resume_path' => $fileName]);
            $resumePath=public_path('Files\\'.$fileName);
            $profile_data = $this->resume_data($resumePath);
            $skills = $profile_data['skills'];
            foreach ($skills as $skill){
                Skill::create([
                    'user_id'=>$user->id,
                    'name'=>$skill,
                    'level'=>5
                ]);
            }
            Profile::create([
                'user_id'=>$user->id,
                'birthdate'=>$profile_data['birthdate'],
                'adress'=>$profile_data['adress'],
                'marital_status'=>$profile_data['marital_status'],
                'military_status'=>$profile_data['military_status'],
                'education'=>$profile_data['education'],
                'education_date'=>null
            ]);
            return response()->json(['token' => $token, 'email' => $request->email]);
        }
    }

    public function resume_data($resumePath)
    {
//        $resumePath = public_path('Files\CV03-10-07welcome to egyptair ME.pdf');
        $path = env('pdf_to_txt'); // 'c:/Program Files/Git/mingw64/bin/pdftotext'
        $text = Pdf::getText($resumePath, $path);
        $phone = $this->extractPhone($text);
        $birthdate = $this->extractBirthdate($text);
        $adress = $this->extractDress($text);
        $Marital = $this->extractMarital($text);
        $Military = $this->extractMilitary($text);
        $Education = $this->extractEducation($text);
        $Skills = $this->extractSkills($text);
        return [
            'phone' => $phone,
            'birthdate'=>$birthdate,
            'adress'=>$adress,
            'marital_status'=>$Marital,
            'military_status'=>$Military,
            'education'=>$Education,
            'skills'=>$Skills,
        ];
    }
    private function extractPhone($text)
    {
        // Search for phone number patterns
        $regex = '/\b(?:\+?(\d{1,3})[ -]?)?(\d{3}(?:[ -]?\d{3}){2})\b/';
        preg_match_all($regex, $text, $matches);

        if($matches[0]){
            return $matches[0][0];
        }
        else
        {
            // get any numbers start with 01
            $digitsOnly = preg_replace('/\D/', '', $text);
            preg_match('/01(\d{6,9})/', $digitsOnly, $matches);
            return $matches[0]??null;
        }
    }
    private function extractBirthdate($text)
    {
        $regex = '/\b(\d{1,2})[-\/](\d{1,2})[-\/](\d{2,4})\b/';
        preg_match_all($regex, $text, $matches);
        return $matches[0][0]??null;
    }
    private function extractDress($text)
    {
        $regex = '/\d+\s+[\w\s]+\s+(?:St\.?|Street|Ave\.?|Avenue|Blvd\.?|Boulevard|Dr\.?|Drive|Ln\.?|Lane|Road|Ct\.?|Court)/i';
        preg_match_all($regex, $text, $matches);
        return $matches[0][0]??null;
    }
    private function extractMarital($text)
    {
        $regex = '/(\b(?:single|married|divorced|widowed)\b)/i';
        preg_match($regex, $text, $match);
        return $match[1] ?? null;
    }
    private function extractMilitary($text)
    {
        $regex = '/\b(?:active duty|veteran|reserve|completed|finished|retired|discharged)\b/i';;
        preg_match($regex, $text, $match);
        return $match[0] ?? null;
    }
    private function extractEducation($text)
    {
        $regex = '/(Hospital|University|Institute|School|Academy)/i';
        preg_match($regex, $text, $match);
        return $match[0] ?? null;
    }
    private function extractSkills($text)
    {
        $skills = array(
            'programming',
            'data analysis',
            'project management',
            'communication',
            'SolidWorks',
            'Doing Maintenance',
            'Laravel',
            'PHP',
            'html',
            'SQL',
            // Add more skills as needed
        );
        $extractedSkills = array();

        foreach ($skills as $skill) {
            $pattern = '/\b' . preg_quote($skill, '/') . '\b/i';
            if (preg_match($pattern, $text)) {
                $extractedSkills[] = $skill;
            }
        }
        if (!empty($extractedSkills)) {
            return $extractedSkills;
        } else {
            return null;
        }
    }
}
