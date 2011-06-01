<?php
/**
 *
 * Copyright (C) 2011 pirminmattmann
 *
 * This file is part of eCamp.
 *
 * eCamp is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * eCamp is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with eCamp.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
 
class RegisterController
	extends \Controller\BaseController
{

	/**
	 * @var \Doctrine\ORM\EntityRepository
	 * @Inject UserRepository
	 */
	private $userRepository;


	/**
	 * @var \Service\UserService
	 * @Inject Service\UserService
	 */
	private $userService;


	public function indexAction()
	{

		$registerForm = new \Form\Register();

		if($id = $this->getRequest()->getParam('id'))
		{
			/** @var $user \Entity\User */
			$user = $this->userRepository->find($id);

			if(!is_null($user) && $user->getState() == \Entity\User::STATE_NONREGISTERED)
			{
				$registerForm->setDefault('email', 		$user->getEmail());
				$registerForm->setDefault('scoutname', 	$user->getScoutname());
				$registerForm->setDefault('firstname', 	$user->getFirstname());
				$registerForm->setDefault('surname', 	$user->getSurname());
			}
		}

		$registerForm->setDefaults($this->getRequest()->getParams());

		$this->view->registerForm = $registerForm;

	}


	public function registerAction()
	{
		$registerForm = new \Form\Register();

		if(!$registerForm->isValid($this->getRequest()->getParams()))
		{
			$this->view->registerForm = $registerForm;
			$this->render("index");
			return;
		}

		
		$email = $registerForm->getValue('email');
		$password = $registerForm->getValue('password1');
		

		/** @var $user \Entity\User */
		$user = $this->userRepository->findOneBy(array('email' => $email));

		if($user == null)
		{
			$user = new Entity\User();
			$user->setEmail($email);

			$this->em->persist($user);
		}
		
		if($user->getState() != Entity\User::STATE_NONREGISTERED)
		{
			$this->_redirect('login', 'login');
			reutrn;
		}

		$user->setUsername($registerForm->getValue('username'));
		$user->setScoutname($registerForm->getValue('scoutname'));
		$user->setFirstname($registerForm->getValue('firstname'));
		$user->setSurname($registerForm->getValue('surname'));
		$user->setState(\Entity\User::STATE_REGISTERED);

		$login = $this->userService->createLogin($user, $password);
		$activationCode = $user->createNewActivationCode();

		$this->em->persist($login);
		$this->em->flush();


		$link = "/register/activate/" . $user->getId() . "/key/" . $activationCode;
		echo "<a href='" . $link . "'>" . $link . "</a>";
		die();
	}


	public function activateAction()
	{
		$id = $this->getRequest()->getParam('id');
		$key = $this->getRequest()->getParam('key');


		if($this->userService->activateUser($id, $key))
		{
			$this->em->flush();

			$this->_forward('index', 'login');
		}
		else
		{
			/** @var $user \Entity\User */
			$user = $this->userRepository->find($id);

			die($user->createNewActivationCode());
		}

	}

}
