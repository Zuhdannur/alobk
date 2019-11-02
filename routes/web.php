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

$router->get('/', function () {
    return 'Welcome To Api visit https://master-konseling.herokuapp.com for more information';
});
$router->get('/test', 'SchedulesController@notification');
$router->group(['prefix'=>'v1/api'], function () use ($router) {

    $router->post('login', 'UsersController@login');
    $router->post('register', 'UsersController@register');

    $router->get('title', 'ArtikelsController@getTitle');
    $router->post('artikel', 'ArtikelsController@create');

    /**
    * Routes for resource sekolah
    */
    $router->get('sekolah', 'SekolahsController@all');
    $router->post('sekolah', 'SekolahsController@add');
    $router->get('sekolah/{id}', 'SekolahsController@get');
    $router->put('sekolah/{id}', 'SekolahsController@put');
    $router->delete('sekolah/{id}', 'SekolahsController@remove');
    $router->get('sekolah/master/month', 'SekolahsController@getDataThisMonth');
    $router->get('sekolah/master/sekolah', 'SekolahsController@getSekolahCount');
    $router->post('sekolah/check', 'SekolahsController@checkSekolahName');

    /**
    * Routes for resource kelas
    */
    $router->get('kelas', 'KelasController@all');
    $router->get('kelas/{id}', 'KelasController@get');
    $router->post('kelas', 'KelasController@add');
    $router->put('kelas/{id}', 'KelasController@put');
    $router->delete('kelas/{id}', 'KelasController@remove');

    $router->group(['middleware' => 'auth'], function () use ($router) {

        $router->group(['namespace' => 'Master'], function () use ($router) {

            $router->group(['prefix' => 'master'], function () use ($router) {
                $router->get('sekolah', 'SekolahController@all');
                $router->post('sekolah', 'SekolahController@post');
                $router->put('sekolah', 'SekolahController@put');
                $router->get('sekolah/count', 'SekolahController@count');
                $router->get('user/admin/count', 'UserController@adminCount');
                $router->get('user/recent', 'UserController@recentActivity');
            });

        });

        $router->group(['namespace' => 'Admin'], function () use ($router) {

            $router->group(['prefix' => 'admin'], function () use ($router) {

            });

        });

        $router->group(['namespace' => 'Supervisor'], function () use ($router) {

            $router->group(['prefix' => 'supervisor'], function () use ($router) {

            });

        });

        $router->group(['namespace' => 'Guru'], function () use ($router) {

            $router->group(['prefix' => 'guru'], function () use ($router) {
                $router->get('diary', 'DiaryController@all');
                $router->get('schedule', 'ScheduleController@all');
                $router->post('schedule/{id}', 'ScheduleController@accept');
            });

        });

        $router->group(['namespace' => 'Siswa'], function () use ($router) {

            $router->group(['prefix' => 'siswa'], function () use ($router) {
                $router->post('schedule', 'ScheduleController@post');
                $router->post('schedule/{id}', 'ScheduleController@cancel');
                $router->get('schedule', 'ScheduleController@all');
                $router->put('schedule/{id}', 'ScheduleController@put');

                $router->put('diary', 'DiaryController@put');
                $router->get('diary', 'DiaryController@all');
                $router->post('diary', 'DiaryController@post');
                $router->delete('diary/{id}', 'DiaryController@remove');
            });

        });

        //profile
        $router->put('user', 'UsersController@put');
        $router->get('user/teacher/student/profile/{id}','UsersController@getStudentInfo');
        $router->post('user/update/image', 'UsersController@updateImageProfile');
        $router->get('user', 'UsersController@all');
        $router->post('user/password', 'UsersController@changePassword');
        $router->get('user/check', 'UsersController@checkUsername');
        $router->get('user/master/account', 'UsersController@getTotalAccount');
        $router->get('user/admin/account', 'UsersController@getTotalAccountBySchool');

        /*Siswa dapat melihat diary*/
        $router->get('diary/student', 'DiariesController@all');
        /*Siswa dapat menambahkan catatan*/
        $router->post('diary/student', 'DiariesController@add');
        /*Siswa dapat menyunting catatan*/
        $router->put('diary/student', 'DiariesController@put');
        /*Siswa dapat menghapus catatan*/
        $router->delete('diary/student/{id}', 'DiariesController@remove');
        /*Guru dapat mendapatkan jumlah catatan siswa*/
        $router->get('diary/student/{id}', 'DiariesController@diaryCount');
        /*Guru dapat membaca catatan siswa*/
        $router->get('diary/teacher', 'DiariesController@readDiary');

        /**
         * Routes for resource schedule
         */
        /*Guru melihat jumlah pengajuan siswa*/
        $router->get('schedule/student/{id}', 'SchedulesController@getStudentScheduleCount');
        /*Siswa dapat menambahkan jadwal*/
        $router->post('schedule/student', 'SchedulesController@add');
        /*Siswa dapat melihat semua jadwal*/
        $router->get('schedule/student', 'SchedulesController@all');
        /*Siswa dapat menyunting jadwal*/
        $router->put('schedule/student', 'SchedulesController@put');
        /*Siswa dapat menghapus jadwal berdasarkan id*/
        $router->delete('schedule/student/{id}', 'SchedulesController@remove');
        /*Siswa dapat membatalkan pengajuan*/
        $router->post('schedule/student/cancel/{id}/{status}', 'SchedulesController@cancel');
        /*Guru & Siswa dapat menyelesaikan pengajuan*/
        $router->post('schedule/finish/{id}', 'SchedulesController@finish');
        /*Guru dapat melihat pengajuan*/
        $router->get('schedule/guru', 'SchedulesController@receive');
        /*Guru & Siswa dapat menyelesaikan pengajuan*/
        $router->post('schedule/guru/accept/{id}', 'SchedulesController@accept');

        $router->delete('schedule', 'SchedulesController@removeAll');
        $router->post('scheduleChannelUrl', 'SchedulesController@updateChannelUrl');

        /**
         * Routes for resource user
         */
        $router->get('user/{id}', 'UsersController@get');
        $router->put('user', 'UsersController@put');
        $router->delete('user/{id}', 'UsersController@remove');

        /**
         * Routes for resource notifikasi
         */
        $router->get('notifikasi', 'NotifikasisController@all');
        $router->get('notifikasi/{id}', 'NotifikasisController@get');
        $router->post('notifikasi', 'NotifikasisController@add');
        $router->put('notifikasi/{id}', 'NotifikasisController@put');
        $router->delete('notifikasi/{id}', 'NotifikasisController@remove');
        $router->delete('notifikasi', 'NotifikasisController@removeAll');
        $router->get('notifikasiPageCount', 'NotifikasisController@notifikasiCount');

        $router->post('updateRead', 'NotifikasisController@read');

        /**
         * Routes for resource riwayat
         */
        $router->get('riwayat', 'RiwayatsController@all');
        $router->get('riwayat/{id}', 'RiwayatsController@get');
        $router->post('riwayat', 'RiwayatsController@add');
        $router->put('riwayat/{id}', 'RiwayatsController@put');
        $router->delete('riwayat/{id}', 'RiwayatsController@remove');
        $router->get('viewRiwayat', 'RiwayatsController@view');

        //Favorite Artikels
        $router->post('favorit', 'ArtikelsController@storeFavorite');
        $router->get('favorit', 'ArtikelsController@getMyFavorite');
        $router->get('favoritCount', 'ArtikelsController@getMyFavoriteCount');
        $router->delete('favorit/{id}/{id_favorit}', 'ArtikelsController@removeMyFavorit');

        $router->post('related', 'ArtikelsController@getRelatedArtikel');

        /**
         * Routes for resource catatan-konseling
         */
        $router->get('catatan-konseling', 'CatatanKonselingsController@all');
        $router->get('catatan-konseling/{id}', 'CatatanKonselingsController@get');
        $router->post('catatan-konseling', 'CatatanKonselingsController@add');
        $router->put('catatan-konseling/{id}', 'CatatanKonselingsController@put');
        $router->delete('catatan-konseling/{id}', 'CatatanKonselingsController@remove');
    });
});
