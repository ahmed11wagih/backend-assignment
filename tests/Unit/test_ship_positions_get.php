<?php

namespace Tests\Unit;

use App\Models\RequestLog;
use Tests\TestCase;

class test_ship_positions_get extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */


    public function test_get_method_search_api()
    {
        $data['mmsi'][] = '311040700';
        $data['mmsi'][] = '247039300';
        $data['time_from'] = '1372700460';
        $data['time_to'] = '1372700580';
        $data['range'] = '2000';
        $data['unit'] = 'mile';
        $data['lon'] = '31.70552';
        $data['lat'] = '34.75784';
        $data['per_page'] = '10';

        $header = array_rand(['application/json','application/hal+json','application/xml','text/csv']);

        $response = $this->json('get','/api/search',$data,['Content-Type : '.$header]);

        $response->assertStatus(200);
    }


    public function test_post_method_search_api()
    {
        $data['mmsi'][] = '311040700';
        $data['mmsi'][] = '247039300';
        $data['time_from'] = '1372700460';
        $data['time_to'] = '1372700580';
        $data['range'] = '2000';
        $data['unit'] = 'mile';
        $data['lon'] = '31.70552';
        $data['lat'] = '34.75784';
        $data['per_page'] = '10';

        $header = array_rand(['application/json','application/hal+json','application/xml','text/csv']);

        $response = $this->json('post','/api/search',$data,['Content-Type : '.$header]);

        $response->assertStatus(200);
    }


    public function test_unsupported_header()
    {
        $response = $this->post('/api/search',[],['Content-Type : application/vnd.api+json']);
        $response->assertStatus(415);
    }


    public function test_bad_request()
    {
        $data['mmsi'][] = '31104070M';

        $response = $this->json('post','/api/search',$data);

        $response->assertStatus(400);
    }



    public function test_api_too_many_requests()
    {
        for($n = 0;$n <= 11;$n++)
        {
            $response = $this->json('post', 'api/search',[]);
        }

        RequestLog::where('REMOTE_ADDR','vendor/phpunit/phpunit/phpunit')->delete();

        $response->assertStatus(429);
    }
}
