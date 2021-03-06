<?php

namespace App\Controllers;

use App\Models\User;
use App\Utils\Paginator;
use App\Utils\Session;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;
use voku\helper\AntiXSS;

class UserController extends BaseController
{
    public function registrationForm(RequestInterface $request, ResponseInterface $response)
    {
        $this->render($response, 'user/register');
    }

    public function registration(RequestInterface $request, ResponseInterface $response)
    {
        if (false === $request->getAttribute('csrf_status')) {
            $this->flash('error', 'Une erreur est survenue pendant l\'envoi du formulaire !');
            return $this->redirect($response, 'user.register.form', []);
        } else {
            $xss_first_name = new AntiXSS();
            $xss_first_name->xss_clean($request->getParam('first_name'));
            $xss_last_name = new AntiXSS();
            $xss_last_name->xss_clean($request->getParam('last_name'));
            $xss_mail = new AntiXSS();
            $xss_mail->xss_clean($request->getParam('mail'));
            $xss_password = new AntiXSS();
            $xss_password->xss_clean($request->getParam('password'));
            $xss_password_repeat = new AntiXSS();
            $xss_password_repeat->xss_clean($request->getParam('password_repeat'));

            if (!$xss_first_name->isXssFound() && !$xss_last_name->isXssFound() && !$xss_mail->isXssFound() && !$xss_password->isXssFound() && !$xss_password_repeat->isXssFound()) {
                $errors = [];

                if (!Validator::stringType()->notEmpty()->validate($request->getParam('first_name'))) {
                    $errors['first_name'] = "Veuillez saisir un prénom valide.";
                }

                if (!Validator::stringType()->notEmpty()->validate($request->getParam('last_name'))) {
                    $errors['last_name'] = "Veuillez saisir un nom valide.";
                }

                if (!Validator::email()->validate($request->getParam('mail'))) {
                    $errors['mail'] = "Veuillez saisir un email valide.";
                }

                if (!Validator::stringType()->notEmpty()->validate($request->getParam('password'))) {
                    $errors['password'] = "Veuillez saisir un mot de passe valide.";
                }

                if (!Validator::stringType()->notEmpty()->validate($request->getParam('password_repeat'))) {
                    $errors['password_repeat'] = "Veuillez saisir un mot de passe de vérification valide.";
                }

                if ($request->getParam('password') !== $request->getParam('password_repeat')) {
                    $errors['password'] = "Le mot de passe ne correspond pas au mot de passe de vérification.";
                }

                if ($request->getParam('password_repeat') !== $request->getParam('password')) {
                    $errors['password_repeat'] = "Le mot de passe de vérification ne correspond pas au mot de passe.";
                }

                if (empty($errors)) {
                    User::create([
                        'id' => uniqid(rand()),
                        'last_name' => $request->getParam('last_name'),
                        'first_name' => $request->getParam('first_name'),
                        'mail' => $request->getParam('mail'),
                        'password' => password_hash($request->getParam('password'), PASSWORD_BCRYPT),
                        'role' => 'user'
                    ]);
                    $this->flash('success', "Inscription réussie avec succès.");
                    return $this->redirect($response, 'user.login.form');
                } else {
                    $this->flash('errors', $errors);
                    return $this->redirect($response, 'user.register.form', []);
                }
            } else {
                $this->flash('error', 'Impossible de traiter le formulaire !');
                return $this->redirect($response, 'user.register.form');
            }
        }
    }

    public function loginForm(RequestInterface $request, ResponseInterface $response)
    {
        $this->render($response, 'user/login');
    }

    public function login(RequestInterface $request, ResponseInterface $response)
    {
        if (false === $request->getAttribute('csrf_status')) {
            $this->flash('error', 'Une erreur est survenue pendant l\'envoi du formulaire !');
            return $this->redirect($response, 'user.login.form', []);
        } else {
            $xss_mail = new AntiXSS();
            $xss_mail->xss_clean($request->getParam('mail'));
            $xss_password = new AntiXSS();
            $xss_password->xss_clean($request->getParam('password'));

            if (!$xss_mail->isXssFound() && !$xss_password->isXssFound()) {
                $errors = [];

                if (!Validator::email()->validate($request->getParam('mail'))) {
                    $errors['mail'] = "Veuillez saisir un email valide.";
                }

                if (!Validator::stringType()->notEmpty()->validate($request->getParam('password'))) {
                    $errors['password'] = "Veuillez saisir un mot de passe valide.";
                }

                if (empty($errors)) {
                    $user = User::where('mail', '=', $request->getParam('mail'))->first();
                    if (!is_null($user)) {
                        if (password_verify($request->getParam('password'), $user->password)) {
                            Session::set('user', $user);
                            $this->flash('success', "Connexion réussie avec succès.");
                            return $this->redirect($response, 'index');
                        } else {
                            $this->flash('error', "Mot de passe incorrect");
                            return $this->redirect($response, 'user.login.form', []);
                        }
                    } else {
                        $this->flash('error', "Adresse email ou mot de passe incorrecte.");
                        return $this->redirect($response, 'user.login.form', []);
                    }
                } else {
                    $this->flash('errors', $errors);
                    return $this->redirect($response, 'user.login.form', []);
                }
            } else {
                $this->flash('error', 'Impossible de traiter le formulaire !');
                return $this->redirect($response, 'user.login.form');
            }
        }
    }

    public function view(RequestInterface $request, ResponseInterface $response)
    {
        $user = User::where('id', '=', Session::get('user')->id)->first();
        $this->render($response, 'user/view', ['user' => $user]);
    }

    public function species(RequestInterface $request, ResponseInterface $response)
    {
        $user = User::where('id', '=', Session::get('user')->id)->first();
        $species = $user->getSpecies()->get();

        $total = count($species);
        $page = (!is_null($request->getParam('page')) ? ($request->getParam('page') - 1) : 0);
        $pagination = Paginator::paginate(Paginator::$perPage, $total, $request->getParam('page'));
        $page = (($page > ($pagination['lastPage'] - 1)) ? ($pagination['lastPage'] - 1) : $page);
        $offset = (Paginator::$perPage * $page);

        $species = $user->getSpecies()
            ->limit(Paginator::$perPage)
            ->offset($offset)
            ->orderBy('name_latin')
            ->get();

        $this->render($response, 'user/species', [
            'species' => $species,
            'pagination' => $pagination
        ]);
    }

    public function logout(RequestInterface $request, ResponseInterface $response)
    {
        Session::unset('user');
        return $this->redirect($response, 'user.login.form');
    }

    public function edit(RequestInterface $request, ResponseInterface $response)
    {
        $user = User::where('id', '=', Session::get('user')->id)->first();
        $this->render($response, 'user/edit', ['user' => $user]);
    }

    public function update(RequestInterface $request, ResponseInterface $response)
    {
        if (false === $request->getAttribute('csrf_status')) {
            $this->flash('error', 'Une erreur est survenue pendant l\'envoi du formulaire !');
            return $this->redirect($response, 'user.edit');
        } else {
            $xss_first_name = new AntiXSS();
            $xss_first_name->xss_clean($request->getParam('first_name'));
            $xss_last_name = new AntiXSS();
            $xss_last_name->xss_clean($request->getParam('last_name'));
            $xss_mail = new AntiXSS();
            $xss_mail->xss_clean($request->getParam('mail'));

            if (!$xss_first_name->isXssFound() && !$xss_last_name->isXssFound() && !$xss_mail->isXssFound()) {
                $errors = [];

                if (!Validator::stringType()->notEmpty()->validate($request->getParam('first_name'))) {
                    $errors['first_name'] = "Veuillez saisir un prénom valide.";
                }

                if (!Validator::stringType()->notEmpty()->validate($request->getParam('last_name'))) {
                    $errors['last_name'] = "Veuillez saisir un nom valide.";
                }

                if (!Validator::email()->validate($request->getParam('mail'))) {
                    $errors['mail'] = "Veuillez saisir un email valide.";
                }

                if (empty($errors)) {
                    $user = User::where('id', '=', Session::get('user')->id)->first();
                    $user->update([
                        'last_name' => $request->getParam('last_name'),
                        'first_name' => $request->getParam('first_name'),
                        'mail' => $request->getParam('mail')
                    ]);
                    $this->flash('success', "Modification du compte réussie avec succès.");
                    return $this->redirect($response, 'user.view');
                } else {
                    $this->flash('errors', $errors);
                    return $this->redirect($response, 'user.edit');
                }
            } else {
                $this->flash('error', 'Impossible de traiter le formulaire !');
                return $this->redirect($response, 'user.edit');
            }
        }
    }

    public function editPassword(RequestInterface $request, ResponseInterface $response)
    {
        $user = User::where('id', '=', Session::get('user')->id)->first();
        $this->render($response, 'user/editPassword', ['user' => $user]);
    }

    public function updatePassword(RequestInterface $request, ResponseInterface $response)
    {
        if (false === $request->getAttribute('csrf_status')) {
            $this->flash('error', 'Une erreur est survenue pendant l\'envoi du formulaire !');
            return $this->redirect($response, 'user.edit');
        } else {
            $xss_password_old = new AntiXSS();
            $xss_password_old->xss_clean($request->getParam('password_old'));
            $xss_password = new AntiXSS();
            $xss_password->xss_clean($request->getParam('password'));
            $xss_password_repeat = new AntiXSS();
            $xss_password_repeat->xss_clean($request->getParam('password_repeat'));

            if (!$xss_password_old->isXssFound() && !$xss_password->isXssFound() && !$xss_password_repeat->isXssFound()) {
                $errors = [];

                $user = User::where('id', '=', Session::get('user')->id)->first();
                if (!password_verify($request->getParam('password_old'), $user->password)) {
                    $errors['password_old'] = "Le mot de passe courant ne correspond pas.";
                }

                if ($request->getParam('password') !== $request->getParam('password_repeat')) {
                    $errors['password'] = "Le nouveau mot de passe ne correspond pas au mot de passe de vérification.";
                }

                if ($request->getParam('password_repeat') !== $request->getParam('password')) {
                    $errors['password_repeat'] = "Le mot de passe de vérification ne correspond pas au mot de passe.";
                }

                if (empty($errors)) {
                    $user->update([
                        'password' => password_hash($request->getParam('password'), PASSWORD_BCRYPT)
                    ]);
                    $this->flash('success', "Modification du mot de passe réussie avec succès.");
                    return $this->redirect($response, 'user.view');
                } else {
                    $this->flash('errors', $errors);
                    return $this->redirect($response, 'user.editPassword');
                }
            } else {
                $this->flash('error', 'Impossible de traiter le formulaire !');
                return $this->redirect($response, 'user.editPassword');
            }
        }
    }

    public function delete(RequestInterface $request, ResponseInterface $response)
    {
        $user = User::where('id', '=', Session::get('user')->id)->first();
        Session::unset('user');
        $user->delete();

        $this->flash('success', "Votre compte a été supprimé avec succès.");
        return $this->redirect($response, 'index');
    }
}