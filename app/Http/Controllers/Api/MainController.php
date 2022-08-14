<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RequestLog;
use App\Models\ShipPosition;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MainController extends Controller
{
    public function search(Request $request)
    {
        if(! in_array($request->header('Content-Type'),['application/json','application/hal+json','application/xml','text/csv']))
        {
            $data['errors']['msg'] = 'API endpoint only supports Content-Type = [application/json, application/hal+json, application/xml, text/csv].';
            return response()->json($data,415);
        }

        $validator = Validator::make($request->all(),
            [
                'mmsi' => 'sometimes|array',
                'mmsi.*' => 'numeric',
                'range' => 'sometimes|numeric|gte:0',
                'unit' => 'sometimes|in:km,mile|required_with:range',
                'lon' => 'sometimes|numeric|required_with:range',
                'lat' => 'sometimes|numeric|required_with:range',
                'time_from' => 'sometimes',
                'time_to' => 'sometimes',
                'per_page' => 'sometimes|numeric|gte:10',
            ]
        );

        if ($validator->fails())
        {
            return response()->json(['errors' => $validator->getMessageBag()],400);
        }

        $log_count = RequestLog::where(function($q)
        {
            if(isset($_SERVER['REMOTE_ADDR'])) $q->where('REMOTE_ADDR',$_SERVER['REMOTE_ADDR']);
            else if($_SERVER['SCRIPT_NAME'] == 'vendor/phpunit/phpunit/phpunit') $q->where('REMOTE_ADDR',$_SERVER['SCRIPT_NAME']); //for unit testing purposes
            if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) $q->orWhere('HTTP_X_FORWARDED_FOR',$_SERVER['HTTP_X_FORWARDED_FOR']);
        })->where('logged_at','>=',now()->subMinute())->count();

        if($log_count > 10)
        {
            $data['errors']['log'] = 'Requests count exceeded limits, limited to 10 per minute.';
            return response()->json($data,429);
        }
        else
        {
            $remote_addr = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : $_SERVER['SCRIPT_NAME']; //for unit testing purposes
            RequestLog::create(['REMOTE_ADDR' => $remote_addr,'HTTP_X_FORWARDED_FOR' => isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : NULL,'logged_at' => now()->toDateTimeString()]);
        }


        $feeds = ShipPosition::where(function($q) use($request)
        {
            if($request->mmsi) $q->whereIn('mmsi',$request->mmsi);
            if($request->range) $q->withinRange($request->only('range','unit','lon','lat'));
            if($request->time_from) $q->where('timestamp','>=',Carbon::parse((integer)$request->time_from)->toDateTimeString());
            if($request->time_to) $q->where('timestamp','<=',Carbon::parse((integer)$request->time_to)->toDateTimeString());
        })->paginate((integer)$request->per_page ?: 10)->withQueryString();

        $data['count']['perPage'] = $feeds->perPage();
        $data['count']['total'] = $feeds->total();
        $data['_links']['self']['href'] = $feeds->url($feeds->currentPage());
        $data['_links']['next']['href'] = $feeds->nextPageUrl();
        $data['_links']['last']['href'] = $feeds->url($feeds->lastPage());
        $data['data'] = $feeds->toArray()['data'];

        switch($request->header('Content-Type'))
        {
            case 'application/json':
                return response()->json($data);
            case 'application/hal+json':
                header('Media-Type : application/hal+json');
                return response()->json($data);
            case 'application/xml':
                return response()->xml($data);
            case 'text/csv':
                return ShipPosition::toCsv($data['data']);
            default:
                return response()->json($data);
        }
    }
}
