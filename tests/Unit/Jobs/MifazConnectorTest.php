<?php

namespace Tests\Unit\Wrapper\Apis;

use Tests\TestCase;
use App\Jobs\MifazConnector;
use App\Search;
use App\TimeRange;
use Carbon\Carbon;

class MifazConnectorTest extends TestCase
{

    public $connector;

    public function setUp(): void
    {
        parent::setUp();
        $this->connector = new MifazConnector(new Search());
    }

    public function testConvertEntryToTrips(): void
    {
        $search = factory(Search::class)->make();
        $search->setAttribute('departure', new TimeRange(['time' => Carbon::create(2020,5,23), 'toleranceInDays' => 1]));

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

        $trip = $this->connector->convertEntryToTrips($entry, $search)[0];
        $this->assertEquals('mifaz-' . $entry['id'] . '-20200524', $trip->id);
        $this->assertEquals('Mifaz', $trip->connector);
        $this->assertEquals($entry['url'], $trip->offer->url);
        $this->assertEquals(53.5686111111, $trip->startPoint->latitude);
        $this->assertEquals(10.0386111111, $trip->startPoint->longitude);
        $this->assertEquals(52.5219444444, $trip->endPoint->latitude);
        $this->assertEquals(13.4102777778, $trip->endPoint->longitude);
        $this->assertEquals($entry['creationdate'], $trip->created->format('Y-m-d H:i:s'));
        $this->assertEquals($entry['lastupdate'], $trip->modified->format('Y-m-d H:i:s'));
        $this->assertEquals($entry['username'], $trip->offer->name);
        $this->assertEquals($entry['imgUser'], $trip->offer->imgUser);
        $this->assertEquals("CAR", $trip->transport->transportType);
    }

    public function testFindCorrespondingDates(): void
    {
        $entry = [
            "times" => "So",
        ];

        $search = factory(Search::class)->make();
        $search->setAttribute('departure', new TimeRange(['time' => Carbon::create(2020,5,23), 'toleranceInDays' => 1]));

        $dates = $this->connector->findCorrespondingDates($entry, $search);
        $this->assertEquals(1, count($dates));
        $this->assertEquals(Carbon::create(2020,05,24), $dates[0]);
    }

    public function testSingleTrip(): void
    {
        $entry = [
            "times" => "",
            "regulary" => "0",
            "journeydate" => "2020-05-23"
        ];

        $search = factory(Search::class)->make();
        $search->setAttribute('departure', new TimeRange(['time' => Carbon::create(2020,5,23), 'toleranceInDays' => 1]));

        $dates = $this->connector->findCorrespondingDates($entry, $search);
        $this->assertEquals(1, count($dates));
        $this->assertEquals(Carbon::create(2020,05,23), $dates[0]);
    }

    public function testType(): void
    {
        $entry = [
            "type" => "0",
        ];

        $search = factory(Search::class)->make();

        $dates = $this->connector->findCorrespondingDates($entry, $search);
        $this->assertEquals(0, count($dates));
    }
}
