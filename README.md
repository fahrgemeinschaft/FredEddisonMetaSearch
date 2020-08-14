# Description of the software


## For local setup:
The software is written in php Laravel. For a local development environment, laradock.io is recommended.

### after installation: 

Start Laradoc: docker-compose up -d nginx redis workspace 

Into the .env (from laravel):

REDIS_CACHE_DB=0 
CACHE_PREFIX= 
CACHE_DRIVER=redis 

To get into the redis: docker-compose exec redis bash and then redis-cli 

### Test the environment:
access http://localhost/api/trip/search/ via POST and enter a search object:

#### Example search object 1:  
{"startPoint":{"location":{"latitude":52.5198535,"longitude":13.4385964}, "radius": "50.0"},"endPoint":{"location":{"latitude":53.5511,"longitude":9.9937}, "radius": "50.0"},"departure":{"time":"2020-08-16T11:06:04. 690Z","toleranceInDays":0},"arrival":null,"page":{"firstIndex":20,"page":0,"pageSize":20},"reoccurDays":null,"availabilityStarts":"2020-08-16T11:06:04. 690Z", "availabilityEnds":null, "tripTypes":[0], "transportTypes":[0], "animals":2, "baggage":1, "gender":2, "organizations":[], "smoking":3} 
 
#### Example Search Object 2:  
{"startPoint":{"location":{"latitude": 52.522,"longitude":13.411}, "radius": "50"},"endPoint":{"location":{"latitude":53.5511,"longitude": 9.9937}, "radius": "50"},"departure":{"time":"2020-08-14T11:06:04. 690Z","toleranceInDays":2},"arrival":null,"page":{"firstIndex":20,"page":0,"pageSize":20},"reoccurDays":null,"availabilityStarts":"2020-08-14T11:06:04. 690Z", "availabilityEnds":null, "tripTypes":[0], "transportTypes":[0], "animals":2, "baggage":1, "gender":2, "organizations":[], "smoking":3} 


