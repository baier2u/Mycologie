<?php
use App\Controllers\AppController;

use App\Controllers\DatabaseController;
use App\Controllers\PdfController;
use App\Controllers\SpecieController;
use App\Controllers\UserController;
use App\Middlewares\AdminMiddleware;
use App\Middlewares\GuestMiddleware;
use App\Middlewares\AuthMiddleware;

$app->get('/', AppController::class . ':index')->setName('index');

$app->group('/user', function() {
    $container = $this->getContainer();

    $this->get('/registration', UserController::class . ':registrationForm')
        ->add(new GuestMiddleware($container))
        ->setName('user.register.form');

    $this->post('/registration', UserController::class . ':registration')
        ->add(new GuestMiddleware($container))
        ->setName('user.register');

    $this->get('/login', UserController::class . ':loginForm')
        ->add(new GuestMiddleware($container))
        ->setName('user.login.form');

    $this->post('/login', UserController::class . ':login')
        ->add(new GuestMiddleware($container))
        ->setName('user.login');

    $this->get('/view', UserController::class . ':view')
        ->add(new AuthMiddleware($container))
        ->setName('user.view');

    $this->get('/species', UserController::class . ':species')
        ->add(new AuthMiddleware($container))
        ->setName('user.species');

    $this->get('/logout', UserController::class . ':logout')
        ->add(new AuthMiddleware($container))
        ->setName('user.logout');

    $this->get('/edit', UserController::class . ':edit')
        ->add(new AuthMiddleware($container))
        ->setName('user.edit');

    $this->post('/update', UserController::class . ':update')
        ->add(new AuthMiddleware($container))
        ->setName('user.update');

    $this->get('/editPassword', UserController::class . ':editPassword')
        ->add(new AuthMiddleware($container))
        ->setName('user.editPassword');

    $this->post('/updatePassword', UserController::class . ':updatePassword')
        ->add(new AuthMiddleware($container))
        ->setName('user.updatePassword');

    $this->get('/delete', UserController::class . ':delete')
        ->add(new AuthMiddleware($container))
        ->setName('user.delete');
});

$app->group('/species', function() {
    $container = $this->getContainer();

    $this->get('/add', SpecieController::class . ':addForm')
        ->add(new AdminMiddleware($container))
        ->setName('species.addForm');

    $this->get('/tmpdb', SpecieController::class . ':tmpdb')
        ->setName('species.tmpdb');

    $this->get('[/]', SpecieController::class . ':index')
        ->setName('species');

    $this->get('/specie', SpecieController::class . ':getSpecie')
        ->setName('species.getSpecie');

    $this->get('/specieFr', SpecieController::class . ':getSpecieFr')
        ->setName('species.getSpecieFr');

    $this->get('/{specie_id}', SpecieController::class . ':view')
        ->setName('species.view');

    $this->get('/{specie_id}/edit', SpecieController::class . ':editForm')
        ->add(new AdminMiddleware($container))
        ->setName('species.editForm');

    $this->post('/{specie_id}/update', SpecieController::class . ':update')
        ->add(new AdminMiddleware($container))
        ->setName('species.update');

    $this->get('/delete/{specie_id}', SpecieController::class . ':deleteOne')
        ->add(new AdminMiddleware($container))
        ->setName('species.deleteOne');

    $this->post('/create', SpecieController::class . ':create')
        ->add(new AdminMiddleware($container))
        ->setName('species.create');
});

$app->group('/pdf', function() {
    $container = $this->getContainer();

    $this->get('/add/{specie_id}', PdfController::class . ':add')
        ->add(new AdminMiddleware($container))
        ->setName('pdf.add');

    $this->get('/listPreview', PdfController::class . ':listPreview')
        ->add(new AdminMiddleware($container))
        ->setName('pdf.listPreview');

    $this->get('/listDownload', PdfController::class . ':listDownload')
        ->add(new AdminMiddleware($container))
        ->setName('pdf.listDownload');

    $this->get('/delete', PdfController::class . ':delete')
        ->add(new AdminMiddleware($container))
        ->setName('pdf.delete');
});

$app->group('/database', function() {
    $container = $this->getContainer();

    $this->get('/download', DatabaseController::class . ':download')
        ->add(new AdminMiddleware($container))
        ->setName('database.download');
});