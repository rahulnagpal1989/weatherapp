<!doctype html>
<html>
    <head>
        <title>Weather App</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
        <script src="https://code.jquery.com/jquery-3.3.1.min.js" type="text/javascript"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
        <script src="https://code.angularjs.org/1.7.2/angular.min.js" type="text/javascript"></script>
        <script src="https://code.angularjs.org/1.7.2/angular-sanitize.min.js" type="text/javascript"></script>
        <style>
            #header{padding:10px;height:100px;border-bottom:1px solid #ccc;}
            .box{border: 1px solid #ccc;border-radius: 3px;margin: 5px;padding: 10px;}
            .temperature{display:none;}
        </style>
    </head>
    <body id="weather" ng-app="weatherApp" ng-controller="weatherController" class="ng-scope" ng-cloak>
        <div class="container">
            <form action="">
                <div class="row" id="header">
                    <div class="col-sm-3">
                        <h3>Weather App</h3>
                    </div>
                    <div class="col-sm-5">
                        <input type="search" class="form-control" name="search_txt" id="search_txt" autocomplete="off" placeholder="Search">
                    </div>
                    <div class="col-sm-1">
                        <button type="submit" id="sub_btn" class="btn btn-primary" ng-click="hitRequest()">Search</button>
                    </div>
                </div>
            </form>
            <div class="row">
                <div class="col-sm-12">
                    <div id="loading"></div>
                </div>
            </div>
            <div class="row">
                <div class="col box main_box" ng-repeat="city in cities">
                    <h4><a href="#/weather/{{city[0].woeid}}">{{city[0].title}}</a></h4>
                    <div class="meta">
                        <h5><img src="https://www.metaweather.com/static/img/weather/{{city[0].temperature[0].weather_state_abbr}}.svg" width="32" /> {{city[0].temperature[0].weather_state_name}}</h5>
                        <h5>Temp: {{city[0].temperature[0].the_temp}}°C</h5>
                        <h5>Min: {{city[0].temperature[0].min_temp}}°C</h5>
                        <h5>Max: {{city[0].temperature[0].max_temp}}°C</h5>
                    </div>
                
                    <div class="row temperature" id="temperature_{{city[0].woeid}}">
                        <div class="col box" ng-repeat="temp in city[0].temperature">
                            <h4>{{temp.applicable_date}}</h4>
                            <h5><img src="https://www.metaweather.com/static/img/weather/{{temp.weather_state_abbr}}.svg" width="32" /> {{temp.weather_state_name}}</h5>
                            <h5>Temperature: from {{temp.min_temp}}°C to {{temp.max_temp}}°C</h5>
                            <h5>Humidity: {{temp.humidity}}%</h5>
                            <h5>Wind: {{temp.wind_speed}}mph</h5>
                            <h5>Visibility: {{temp.visibility}} miles</h5>
                            <h5>Pressure: {{temp.air_pressure}}mb</h5>
                            <h5>Confidence: {{temp.predictability}}%</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            var scope;
            var $citiesArr = new Array('Istanbul', 'Berlin', 'London', 'Helsinki', 'Dublin', 'Vancouver');
            var $arr = new Array();
            var $count = 0;
            var $count2 = 0;
            var app = angular.module('weatherApp', ['ngSanitize']);
            var oldURL = '';
            var days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            
            $(function(){
                scope = angular.element("#weather").scope();
                $('#sub_btn').click(function(e){
                    e.preventDefault();
                    location.href = '#/search/'+$('#search_txt').val();
                });
                fetchCity();
                setInterval(function(){readHash();}, 1000);
            });
            
            app.controller('weatherController', function ($scope, $http) {
                //headr info variables
                //$scope.name = "abc";
                //$scope.alluserListArr = [{dep_name:'',id:'1',msgc:'0',name:'General',type:'2'}] ;
                $scope.cities = [];
            });
            
            function readHash(){
                if(oldURL!=location.hash){
                    oldURL = location.hash;
                    $URL = oldURL.split('/');
                    if(oldURL.indexOf('#/weather')!=-1 && $URL.length==3){
                        $divID = $URL[$URL.length-1];
                        $('.main_box').css('border','none');
                        $('.main_box, .meta').hide();
                        $('#temperature_'+$divID).css('display','flex');
                        $('#temperature_'+$divID).parent().show();
                    }else if(oldURL.indexOf('#/search')!=-1 && $URL.length==3){
                        $count = $count2 = 0;
                        $arr = new Array();
                        scope.$apply(function(){
                            scope.cities = $arr;
                        });
                        $('#loading').html('Searching...');
                        searchCity($('#search_txt').val(), 0);
                    }else{
                        $('.main_box').css('border','1px solid #ccc');
                        $('.main_box, .meta').show();
                        $('.main_box .temperature').hide();
                        if($('.main_box').length==1){
                            fetchCity();
                        }
                    }
                }
            }
            
            function fetchCity(){
                $count = $count2 = 0;
                $arr = new Array();
                scope.$apply(function(){
                    scope.cities = $arr;
                });
                $('#loading').html('Fetching...');
                for(city in $citiesArr){
                    searchCity($citiesArr[city], $citiesArr.length-1);
                }
            }
            
            function searchCity(city, num){
                $.get("weather.php?command=search&keyword="+city, function (response, status) {
                    $.get("weather.php?command=location&woeid="+response[0].woeid, function (response2, status) {
                        for(var i in response2.consolidated_weather){
                            response2.consolidated_weather[i].applicable_date = days[new Date(response2.consolidated_weather[i].applicable_date).getDay()];
                        }
                        $arr[$count2][0].temperature = response2.consolidated_weather;
                        if(num==$count2){
                            $('#loading').html('');
                            scope.$apply(function(){
                                scope.cities = $arr;
                            });
                        }
                        $count2++;
                    });
                    $arr[$count++] = response;
                });
            }
        </script>
    </body>
</html>