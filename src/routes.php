<?php

Route::get(config('instagram-feed.auth_callback_route'), 'JustBetter\InstagramFeed\AccessTokenController@handleRedirect');