<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class WeatherController extends Controller
{
    public function index(Request $request)
    {
//        $ipAddress = $request->ip(); //for production
//        $ipAddress = '67.250.186.196';
        $ipAddress = '197.57.128.155';  // for testing on local machine

        $location = $this->getUserLocation($ipAddress);
        $weatherData = $this->fetchWeatherData($location['latitude'], $location['longitude']);
        $weatherData['capital']=$location['capital'];

        return response()->json($weatherData);
    }
//    get coordinates by ip adress
    private function getUserLocation($ipAddress)
    {
            $apiKey = env('YOUR_IPSTACK_API_KEY');
            $client = new Client();
            $url = "http://apiip.net/api/check?ip=.$ipAddress.'&accessKey='.$apiKey.''";
            $response = $client->get($url);


            $location = json_decode($response->getBody(), true);
            $latitude = $location['latitude'];
            $longitude = $location['longitude'];
            $capital = $location['capital'];

            return compact('latitude','capital', 'longitude');
    }
    // get weather data by coordinates
    private function fetchWeatherData($latitude, $longitude)
    {
        $apiKey = env('YOUR_WEATHER_API_KEY');
        $client = new Client();

        $url = "https://api.openweathermap.org/data/2.5/weather?lat={$latitude}&lon={$longitude}&appid={$apiKey}";
        $response = $client->get($url);
        $weatherData = json_decode($response->getBody(), true);

        $temperature = $weatherData['main']['temp'];
        $humidity = $weatherData['main']['humidity'];
        $regionName = $weatherData['name'];
        $wind_speed = $weatherData['wind']['speed'];
        $condition =$weatherData['weather'][0]['description'];

            return compact('temperature', 'humidity','wind_speed','regionName','condition');
    }
}
