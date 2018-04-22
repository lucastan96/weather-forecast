<?php
session_start();

$unit_id = 1;
$message = "Log in to your account to save your temperature unit preferences!";

$query1 = "SELECT unit_id FROM members WHERE member_id = :member_id";
$statement1 = $db->prepare($query1);
$statement1->execute(array(":member_id" => $_SESSION["weather_login"]));
$results1 = $statement1->fetch();
$statement1->closeCursor();

$unit_id = $results1["unit_id"];
$message = "This project uses PHP and the Dark Sky API to display the weather forecast.";

if (isset($_GET['location'])) {
    $query = $_GET["location"];
    $location = htmlentities($_GET["location"]);
    $location = str_replace(" ", "+", $location);
    $geocode_url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . $location . "&key=API_KEY_HERE";
    $location_data = json_decode(file_get_contents($geocode_url));

    $coordinates = $location_data->results[0]->geometry->location;
    $coordinates = $coordinates->lat . ',' . $coordinates->lng;

    $place = $location_data->results[0]->formatted_address;
} else {
    $location = "";
    $coordinates = "53.9795071,-6.3752017";
    $place = "Dundalk, Co. Louth, Ireland";
}

$api_url = "https://api.darksky.net/forecast/API_KEY_HERE/" . $coordinates . "?exclude=minutely";
$forecast = json_decode(file_get_contents($api_url));

$temperature_current = round($forecast->currently->temperature);
$apparent_temperature_current = round($forecast->currently->apparentTemperature);
$summary_current = $forecast->currently->summary;
$windspeed_current = round($forecast->currently->windSpeed);
$humidity_current = $forecast->currently->humidity * 100;
$chance_precip_current = $forecast->currently->precipProbability * 100;

$time_current = $forecast->currently->time;
$time_current = date("g:i a", $time_current);

date_default_timezone_set($forecast->timezone);

function celsius($temp) {
    return round(($temp - 32) * 5 / 9);
}

function fahrenheit($temp) {
    return round($temp * 1.8 + 32);
}

function get_icon($icon) {
    if ($icon === "clear-day") {
        $the_icon = '<canvas class="i-weather clear-day"></canvas>';
        return $the_icon;
    } else if ($icon === "clear-night") {
        $the_icon = '<canvas class="i-weather clear-night"></canvas>';
        return $the_icon;
    } else if ($icon === "rain") {
        $the_icon = '<canvas class="i-weather rain"></canvas>';
        return $the_icon;
    } else if ($icon === "snow") {
        $the_icon = '<canvas class="i-weather snow"></canvas>';
        return $the_icon;
    } else if ($icon === "sleet") {
        $the_icon = '<canvas class="i-weather sleet"></canvas>';
        return $the_icon;
    } else if ($icon === "wind") {
        $the_icon = '<canvas class="i-weather wind"></canvas>';
        return $the_icon;
    } else if ($icon === "fog") {
        $the_icon = '<canvas class="i-weather fog"></canvas>';
        return $the_icon;
    } else if ($icon === "cloudy") {
        $the_icon = '<canvas class="i-weather cloudy"></canvas>';
        return $the_icon;
    } else if ($icon === "partly-cloudy-day") {
        $the_icon = '<canvas class="i-weather partly-cloudy-day"></canvas>';
        return $the_icon;
    } else if ($icon === "partly-cloudy-night") {
        $the_icon = '<canvas class="i-weather partly-cloudy-night"></canvas>';
        return $the_icon;
    } else {
        $the_icon = "<i class='wi wi-thermometer wi-fw'></i>";
        return $the_icon;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Weather Forecast</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <link rel="stylesheet" href="styles/weather-icons.min.css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="styles/index.css">
    </head>
    <body>
        <nav class="navbar navbar-dark bg-dark fixed-top">
            <div>
                <a class="navbar-brand" href="#">
                    <i class="wi wi-day-cloudy d-inline-block mr-2" alt=""></i>
                    <strong>Weather Forecast</strong>
                </a>
                <a class="navbar-text" href="https://darksky.net/dev/docs" target="_blank">Created with Dark Sky API</a>
            </div>
            <div>
                <button class="btn btn-warning btn-view"><i class='wi wi-thermometer wi-fw'></i><span></span></button>
            </div>
        </nav>
        <h1>Dark Sky Forecast</h1>
        <h2 class="bg-secondary text-white text-center h5" style="margin-top: 57px; background: #007bff; padding-top: 10px; padding-bottom: 10px"><?php echo $message; ?></h2>
        <main class="container">
            <form method = "get">
                <label class = "text-white display-3 mt-2" for = "location"><i>Search</i></label>
                <div class = "input-group">
                    <input class = "form-control" type = "text" name = "location" placeholder = "Enter a location to check the weather..." aria-label = "Enter a location" value = "<?php echo $query; ?>">
                    <div class = "input-group-append">
                        <button class = "btn btn-warning" type = "submit"><i class = "fa fa-search" aria-hidden = "true"></i></button>
                    </div>
                </div>
            </form>
            <?php if (!isset($_POST["location"]) || $location_data->status === "OK") {
            ?>
            <h2 class="text-center text-white" id="place">Weather in <i><u><?php echo $place; ?></u></i></h2>
            <p class="lead text-center text-white mb-5">Last updated on <?php echo $time_current; ?><a class="refresh ml-3 text-white" href='javascript:window.location.reload(true)' title='Refresh'><i class='fa fa-refresh' aria-hidden='true'></i></a></p>
            <div class="row temp-current-hour">
                <div class="col-sm-5">
                    <div class="card text-white bg-primary p-5 temp-current">
                        <h2>It's now</h2>
                        <canvas id="i-current" class="<?php echo ($forecast->currently->icon); ?>"></canvas>
                        <div class="card-main mt-4">
                            <h3 class="temp-celsius display-2"><?php echo celsius($temperature_current); ?>&deg;C</h3>
                            <h3 class="temp-fahrenheit display-2"><?php echo $temperature_current; ?>&deg;F</h3>
                        </div>
                        <h3 class="mt-2 temp-fahrenheit">Feels Like <?php echo $apparent_temperature_current; ?>&deg;F</h3>
                        <h3 class="mt-2 temp-celsius">Feels Like <?php echo celsius($apparent_temperature_current); ?>&deg;C</h3>
                        <p><strong><?php echo $summary_current; ?></strong></p>
                        <p class="lead mt-3">Chance of Rain: <?php echo $chance_precip_current; ?>%</p>
                        <p class="lead">Humidity: <?php echo $humidity_current; ?>%</p>
                        <p class="lead">Wind Speed: <?php echo $windspeed_current; ?> <abbr title="miles per hour">MPH</abbr></p>
                    </div>  
                </div>
                <div class="col-sm-7">
                    <div class="card">
                        <ul class="list-group list-group-flush temp-hour bg-light">
                            <?php
    $i = 0;
    foreach ($forecast->hourly->data as $hour):
                            ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <p class="lead m-0">
                                    <?php echo date("h:00 A", $hour->time); ?>
                                </p>
                                <p class="lead m-0 temp-celsius">
                                    <i class='wi wi-thermometer wi-fw'></i><?php echo celsius($hour->temperature); ?>&deg; <abbr title='Celsius'>C</abbr>
                                </p>
                                <p class="lead m-0 temp-fahrenheit">
                                    <i class='wi wi-thermometer wi-fw'></i><?php echo round($hour->temperature); ?>&deg; <abbr title='Fahrenheit'>F</abbr>
                                </p>
                                <p class="lead m-0">
                                    <span class="sr-only">Chance of Rain</span><i class='wi wi-raindrop wi-fw'></i><?php echo $hour->precipProbability * 100; ?>%
                                </p>
                                <p class="lead m-0">
                                    <?php echo get_icon($hour->icon); ?>
                                </p>
                            </li>
                            <?php
    $i++;
    if ($i == 12) {
        break;
    }
    endforeach;
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
            <h3 class='text-white text-center mt-5 mb-3'>Days Ahead</h3>
            <div class="temp-daily">
                <div class="card-columns">
                    <?php
    $i = 0;
    foreach ($forecast->daily->data as $day):
    if ($i != 0) {
        $average_temp = (round($day->temperatureHigh) + round($day->temperatureLow)) / 2;
                    ?>
                    <div class="card border-dark p-5 mb-4">
                        <h2 class="h4 mb-5">
                            <?php echo date("l", $day->time); ?>
                        </h2>
                        <span class=""><?php echo get_icon($day->icon); ?></span>
                        <div class="card-main mt-4">
                            <h3 class="display-4 temp-celsius"><?php echo celsius($average_temp); ?>&deg;C</h3>
                            <h3 class="display-4 temp-fahrenheit"><?php echo round($average_temp); ?>&deg;F</h3>
                        </div>
                        <p class="lead mt-3">
                            Chance of Rain: <?php echo $day->precipProbability * 100; ?>%
                        </p>
                        <p class="lead">
                            Humidity: <?php echo $day->humidity * 100; ?>%
                        </p>
                        <div class="d-flex justify-content-between">
                            <p class="lead">
                                Hi <?php echo round($day->temperatureHigh); ?>&deg;
                            </p>
                            <p class="lead">
                                Lo <?php echo round($day->temperatureLow); ?>&deg;
                            </p>
                        </div>
                        <p class="mt-2">
                            <strong><?php echo $day->summary; ?></strong>
                        </p>
                    </div>
                    <?php
    }
    $i++;
    if ($i == 7) {
        break;
    }
    endforeach;
                    ?>
                </div>
            </div>
            <?php } else { ?>
            <h2 class="text-white text-center">Oops, no results found. Check your search!</h2>
            <?php } ?>
        </main>
        <footer class="mb-5">
            <p class="text-white text-center">Designed by Lucas. &#169; <?php echo date("Y"); ?> All Rights Reserved.</p>
        </footer>
    </body>
    <script src="http://code.jquery.com/jquery-latest.js"></script>
    <script src="scripts/skycons.js"></script>
    <script>
        var icons = new Skycons;
        var icon_current = new Skycons({"color": "white"});
        var list = ["clear-day", "clear-night", "partly-cloudy-day", "partly-cloudy-night", "cloudy", "rain", "sleet", "snow", "wind", "fog"], i;

        for (i = list.length; i--; ) {
            var weatherType = list[i], elements = document.getElementsByClassName(weatherType);
            for (e = elements.length; e--; ) {
                icons.set(elements[e], weatherType);
            }
        }

        var li = document.getElementById('i-current');
        icon_current.add("i-current", li.className);

        icons.play();
        icon_current.play();

        $("#i-current").attr({"width": "128", "height": "128"});
        $(".temp-hour .i-weather").attr({"width": "30", "height": "30"});
        $(".temp-daily .i-weather").attr({"width": "128", "height": "128"});
    </script>
    <script>
        var unit_id = <?php echo $unit_id; ?>;

        if (unit_id === 1) {
            $(".temp-current .temp-fahrenheit, .temp-hour .temp-fahrenheit, .temp-daily .temp-fahrenheit").css("display", "none");
            $(".btn-view span").text("View in Fahrenheit");
        } else {
            $(".temp-current .temp-celsius, .temp-hour .temp-celsius, .temp-daily .temp-celsius").css("display", "none");
            $(".btn-view span").text("View in Celsius");
        }

        $(".btn-view").on('click', function () {
            if ($(".btn-view span").text() == "View in Fahrenheit") {
                $(".btn-view span").text("View in Celsius");
                $(".temp-fahrenheit").css("display", "block");
                $(".temp-celsius").css("display", "none");
                unit_id = 2;
            } else {
                $(".btn-view span").text("View in Fahrenheit");
                $(".temp-celsius").css("display", "block");
                $(".temp-fahrenheit").css("display", "none");
                unit_id = 1;
            }

            $.ajax({
                url: "unit_p.php",
                type: "POST",
                data: {
                    unit_id: unit_id
                }
            });
        });
    </script>
</html>
