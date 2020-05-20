<?php

namespace Tests\Unit\Wrapper\Apis;

use Tests\TestCase;
use App\Jobs\MifazConnector;
use App\Search;

class MifazConnectorTest extends TestCase
{

    public $connector;

    public function setUp(): void
    {
        parent::setUp();
        $this->connector = new MifazConnector(new Search());
    }

    public function testConvertEntryToTrip(): void
    {
        $entry = [
            "id" => "051035017019086237029101",
            "url" => "https://www.mifaz.de/de/eintrag/051035017019086237029101",
            "startID" => "87",
            "startloc" => "Hamburg",
            "startcoord" => "53.5686111111 10.0386111111",
            "goalID" => "12",
            "goalloc" => "Berlin",
            "goalcoord" => "52.5219444444 13.4102777778",
            "stopovernames" => [],
            "stopovercoords" => [],
            "creationdate" => "2018-03-27 23:21:58",
            "lastupdate" => "2019-12-25 23:07:15",
            "type" => "1",
            "regulary" => "1",
            "username" => "k.w.wenz",
            "imgUser" => "",
            "byTrain" => "0",
            "distToStart" => 0,
            "distToGoal" => 0,
            "mobileHash" => "591e7350a1a5762168285a7c1f93cd35",
            "emailHash" => "588c7d7ea9d1c2a1c4e25ea2b1ce0d6c",
            "times" => "So",
            "starttimebegin" => "22:00",
            "starttimeend" => "22:15"
        ];

        $trip = $this->connector->convertEntryToTrip($entry, 'Foo');
    }
}
