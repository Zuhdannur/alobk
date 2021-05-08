<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/



/**
 * Routes for resource user
 */
$router->get('/', function(){
    return view('greeting', array());
});

$router->group(['prefix'=>'v1/api'], function () use ($router) {

    $router->post('login', 'UsersController@login');
    $router->post('register', 'UsersController@register');
    $router->get('cron', 'MastersController@cronJob');
    $router->get('list-school','UsersController@getSchool');

    $router->post('import','ImportDataController@import');
    $router->post('download','ImportDataController@downloadExampleFile');

    /**
    * Routes for resource sekolah
    */

    $router->group(['prefix' => 'sekolah'], function () use ($router) {
        $router->get('sekolah', 'SekolahsController@all');
        $router->post('sekolah', 'SekolahsController@add');
        $router->get('sekolah/{id}', 'SekolahsController@get');
        $router->put('sekolah/{id}', 'SekolahsController@put');
        $router->delete('sekolah/{id}', 'SekolahsController@remove');
        $router->get('sekolah/master/month', 'SekolahsController@getDataThisMonth');
        $router->get('sekolah/master/sekolah', 'SekolahsController@getSekolahCount');
        $router->post('sekolah/check', 'SekolahsController@checkSekolahName');
    });

    $router->group(['middleware' => 'auth'], function () use ($router) {

        $router->group(['namespace' => 'Master'], function () use ($router) {

            $router->group(['prefix' => 'master'], function () use ($router) {
                $router->get('sekolah', 'SekolahController@all');
                $router->post('sekolah', 'SekolahController@post');
                $router->put('sekolah/{id}', 'SekolahController@put');
                $router->delete('sekolah/{id}', 'SekolahController@remove');
                $router->get('sekolah/count', 'SekolahController@count');
                $router->get('sekolah/count/type', 'SekolahController@countSchool');
                $router->get('sekolah/recent', 'SekolahController@all');

                $router->get('user/admin/count', 'UserController@adminCount');
                $router->get('user/admin', 'UserController@getAdmin');
                $router->post('user/register', 'UserController@register');
                $router->delete('user/admin/{id}', 'UserController@remove');
                $router->get('user/admin/recent', 'UserController@all');
                $router->get('user/admin/school/count', 'UserController@countAdminInEverySchool');

                $router->get('article/recent', 'ArticleController@all');
                $router->post('article', 'ArticleController@post');
                $router->put('article/{id}', 'ArticleController@put');
                $router->delete('article/{id}', 'ArticleController@delete');

                $router->get('feed/count', 'FeedController@count');
                $router->get('feed', 'FeedController@all');
                $router->delete('feed', 'FeedController@deleteAll');


                //Get Diary
            });

        });

        $router->group(['namespace' => 'Admin'], function () use ($router) {

            $router->group(['prefix' => 'admin'], function () use ($router) {
                $router->get('user/count', 'UserController@getAdminCount');
                $router->get('user/recent', 'UserController@recentActivity');
                $router->get('users', 'UserController@getUsers');
                $router->get('users-web','UserController@getUsersWeb');
                $router->delete('users/{id}', 'UserController@remove');

                $router->get('diary/generate', 'ScheduleController@generateDiary');
            });

        });

        $router->group(['namespace' => 'Supervisor'], function () use ($router) {

            $router->group(['prefix' => 'supervisor'], function () use ($router) {
                $router->get('schedule/count', 'ScheduleController@getTotalSchedule');
                $router->get('schedule/count/today', 'ScheduleController@getTotalToday');
                $router->get('schedule/recent', 'ScheduleController@lastFeed');
                $router->get('schedule/generate', 'ScheduleController@generateScheduleTest');
                $router->get('diary/generate', 'ScheduleController@generateDiary');

                $router->get('schedule/info', 'ScheduleController@getScheduleByAktif');
                $router->get('schedule', 'ScheduleController@getSchedule');
                // $router->get('schedule/pending', 'ScheduleController@getScheduleByPending');
                // $router->get('schedule/finish', 'ScheduleController@getScheduleByEnded');
                // $router->get('schedule/canceled', 'ScheduleController@getScheduleByCanceled');
            });

        });

        $router->group(['namespace' => 'Guru'], function () use ($router) {

            $router->group(['prefix' => 'guru'], function () use ($router) {
                $router->get('diary', 'DiaryController@all');
                $router->get('schedule', 'ScheduleController@all');
                $router->get('schedule/get/{id}', 'ScheduleController@get');
                $router->get('schedule/total', 'ScheduleController@getScheduleFinished');
                $router->post('accept/{id}', 'ScheduleController@accept');
                $router->post('accept/update/{id}', 'ScheduleController@updateThenAccept');
                $router->put('schedule/finish/{id}', 'ScheduleController@finish');

                $router->get('riwayat', 'ScheduleController@riwayat');

                $router->get('student/profile/{id}','UserController@getStudentInfo');
                $router->get('student/diary/{id}','DiaryController@getStudentDiaryCount');
                $router->get('student/schedule/{id}','ScheduleController@getStudentScheduleCount');

                $router->get('schedule/jadwal/pending', 'ScheduleController@jadwalPending');
                $router->get('schedule/calculate', 'ScheduleController@calculateRating');
                $router->get('schedule/jadwal/aktif', 'ScheduleController@jadwalAktif');
                $router->get('schedule/obrolan/pending', 'ScheduleController@obrolanPending');
                $router->get('schedule/obrolan/aktif', 'ScheduleController@obrolanAktif');
                $router->post('schedule/konselor/update','ScheduleController@changeKonselor');

                $router->get('siswa','SiswaController@getAllSiswa');
                $router->get('konseling/active','ScheduleController@getKonselingAktif');
            });

        });


        $router->group(['prefix' => 'chat'], function () use ($router) {
            $router->post('send', 'ChatController@send');
        });

        $router->group(['namespace' => 'Siswa'], function () use ($router) {

            $router->group(['prefix' => 'siswa'], function () use ($router) {
                //Helper delete later
                $router->get('schedule/get/all', 'ScheduleController@all');
                $router->put('schedule/update/switch', 'ScheduleController@update');
                $router->delete('schedule/delete/{id}', 'ScheduleController@deleteHelper');
                $router->post('schedule/insert/{id}', 'ScheduleController@insertHelper');
                $router->post('schedule/update/{id}', 'ScheduleController@updateHelper');
                $router->post('schedule/update/time/{id}', 'ScheduleController@updateTimeHelper');

                $router->post('schedule', 'ScheduleController@post');
                $router->get('schedule/get/{id}', 'ScheduleController@get');
                $router->get('schedule/total', 'ScheduleController@getScheduleFinished');
                $router->post('schedule/cancel/{id}', 'ScheduleController@cancel');
                $router->post('schedule/accept/{id}', 'ScheduleController@accept');
                $router->post('schedule/request/cancel/{id}', 'ScheduleController@cancelRequestSchedule');
                $router->put('schedule/{id}', 'ScheduleController@put');
                $router->put('schedule/finish/{id}', 'ScheduleController@finish');
                $router->get('riwayat', 'ScheduleController@riwayat');

                $router->get('schedule/jadwal/pending', 'ScheduleController@jadwalPending');
                $router->get('schedule/jadwal/aktif', 'ScheduleController@jadwalAktif');
                $router->get('schedule/obrolan/pending', 'ScheduleController@obrolanPending');
                $router->get('schedule/obrolan/aktif', 'ScheduleController@obrolanAktif');

                $router->post('catatan-konseling', 'CatatanKonselingController@post');

                $router->put('diary', 'DiaryController@put');
                $router->get('diary', 'DiaryController@all');
                $router->post('diary', 'DiaryController@post');
                $router->delete('diary/{id}', 'DiaryController@remove');

                $router->get('article', 'ArticleController@all');
                $router->post('article', 'ArticleController@storeFavorite');
                $router->get('favorite', 'ArticleController@getFavorite');
                $router->get('article/{id}','ArticleController@getDetail');

                $router->post('feedback', 'CatatanKonselingController@post');
                $router->get('feedback/{id}', 'CatatanKonselingController@get');

                $router->get('term_and_condition','TermAndConditionController@getCondition');
                $router->post('term_and_condition/update','TermAndConditionController@update');

                $router->get('konseling/active','ScheduleController@getKonselingAktif');

            });
        });

        $router->post('guru','UsersController@getAllKonselor');
        $router->get('guru/{id}','UsersController@getDetailKonselor');
        $router->get('guru-list','UsersController@getAllPagingKonselor');
        //profile
        $router->put('user', 'UsersController@put');
        $router->get('user', 'UsersController@all');
        $router->post('user/update/image', 'UsersController@updateImageProfile');
        $router->post('user/password', 'UsersController@changePassword');
        $router->get('user/check', 'UsersController@checkUsername');

        //Import

        /**
         * Routes for resource user
         */
        $router->get('user/{id}', 'UsersController@get');
        $router->put('user', 'UsersController@put');
        $router->delete('user/{id}', 'UsersController@remove');


    });
});
