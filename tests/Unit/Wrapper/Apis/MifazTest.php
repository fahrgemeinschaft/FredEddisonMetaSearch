<?php

namespace Tests\Unit\Wrapper\Apis;

use Tests\TestCase;
use App\Wrapper\Apis\Mifaz;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\App;


class MifazTest extends TestCase
{

    public function testGetEntries()
    {

        $container = [];

        $this->app->bind(Client::class, function ($app) use (&$container) {
            $mock = new MockHandler([
                new Response(200, [], '{"entries":[{"id":"139248215078134003078205","url":"https://www.mifaz.de/de/eintrag/139248215078134003078205","startID":"8","startloc":"München","startcoord":"48.138888888911.5738888889","goalID":"46452","goalloc":"Rotterdam","goalcoord":"51.92224957734.479675293","stopovernames":[],"stopovercoords":[],"creationdate":"2020-02-2915:49:12","lastupdate":"2020-02-2915:49:17","type":"2","regulary":"1","username":"Jess87","imgUser":"","byTrain":"0","distToStart":0,"distToGoal":22830,"emailHash":"c2d8f8f12db22556639f49244646262b","times":"","starttimebegin":"00:00","starttimeend":"23:59"}],"startid":"8","goalid":"47341"}'),
            ]);

            $history = Middleware::history($container);
            $handlerStack = HandlerStack::create($mock);
            $handlerStack->push($history);

            return new Client(['base_uri' => Mifaz::API_ENDPOINT, 'handler' => $handlerStack]);
        });

        $m = new Mifaz();
        $entries = $m->getEntries(48.139, 11.574,52.522,13.410);

        $transaction = $container[0];
        $lastUri = (string) $transaction['request']->getUri();

        $this->assertEquals(1, count($entries));
        $this->assertEquals(8, $entries[0]['startID']);
        $this->assertEquals("https://api.mifaz.de/request/?radius=10&tolerance=0&f=getEntries&startlongitude=11.574&startlatitude=48.139&goallatitude=52.522&goallongitude=13.41", $lastUri);

    }
}
