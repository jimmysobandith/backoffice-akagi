<?php
/**
 * Auteur: SOBANDITH Jimmy
 * Société: Warmbee (Deliv's SARL)
 * Date: 2015
 * Desc: Contrôleur de la page de login
 */
use Phalcon\Mvc\Controller,
	Phalcon\Mvc\View;

	class ProfilmdpController extends ControllerBase
{
	/**
	 * Initialisation du contrôleur
	 * Chargement du fichier de langue correspondant au contrôleur
	 * Récupération du titre dans le fichie de langue du contrôleur
	 * Appel de l'initialisation du contrôleur parent
	 */
    public function initialize()
    {
		//Chargement du fichier de langue correspondant au contrôleur
        $this->loadCustomTrans('profilmdp');
		
		//Récupération du titre dans le fichie de langue du contrôleur
		Phalcon\Tag::setTitle($this->t['title']);
		
		//Appel de l'initialisation du contrôleur parent
        parent::initialize();
    }

	
	/**
	 * Action principale
	 */
    public function indexAction()
    {
		$this->view->setRenderLevel(View::LEVEL_NO_RENDER);
		
		$this->log('');
		
		//Réception des valeurs
		$_POST = json_decode(file_get_contents('php://input'), true);
		$identifiant = $this->request->getPost('identifiant', 'string');
		$mdp_actuel = $this->request->getPost('mdpactuel', 'string');
		$nouveau_mdp = $this->request->getPost('nouveaumdp', 'string');
		$confirme_mdp = $this->request->getPost('confirmemdp', 'string');
		
		$reussite = 0; //Connexion réussie ou pas
		
		//Récupération de l'employé en base de données
		$employes = $this->modelsManager->createBuilder()
				->from(array('Employes'))
				->where('Employes.identifiant = "'.$identifiant.'"')
				->limit('1')
				->getQuery()
				->execute();
				
		if (count($employes) == 0)
		{
			$reussite = 4;
		}
		else
		{
			//Employé trouvé
			$utilisateur = $employes[0];
		}
		
		//Vérification du mot de passe
		if (! password_verify($mdp_actuel, $utilisateur->pass))
		{
			if($reussite != 4)
			{
				$reussite = 2;
			}
		}
		else
		{
			$reussite = 1;
		}
		
		//Vérification du mot de passe de confirmation
		if($nouveau_mdp != $confirme_mdp)
		{
			$reussite == 3;
		}
		
		if($reussite == 1)
		{
			//Hashage du mot de passe
			$timeTarget = 0.2; 
 
				$cost = 9;
				do {
					$cost++;
					$start = microtime(true);
					password_hash($nouveau_mdp, PASSWORD_BCRYPT, ["cost" => $cost]);
					$end = microtime(true);
				} while (($end - $start) < $timeTarget);
				
				$hash = password_hash($nouveau_mdp,PASSWORD_BCRYPT,['cost' => $cost]);
				
				
			//Mise à jour en base de données
			$utilisateur->pass = $hash;
			
			$saved = $utilisateur->save();
			
			if ($saved == false)
			{
				$reussite = 4;
			}
		}
		
		//Construction du JSON
		$resultat = new stdClass();
		
		$resultat->reussite = $reussite;
		
		$json = json_encode($resultat);
		
		echo $json;
		
		return true;
	}

	/**
	 * Changement de langue
	 * Rechargement des traductions si nécessaire
	 * Retour à la dernière page
	 */
    public function setLanguageAction($language='')
    {
        //Rechargement des traductions si nécessaire
        if ($language == 'fr') {
            $this->session->set('language', $language);
            $this->loadMainTrans();
            $this->loadCustomTrans('applogin');
        }

        //Retour à la dernière page
        $referer = $this->request->getHTTPReferer();
        if (strpos($referer, $this->request->getHttpHost()."/")!==false) {
            return $this->response->setHeader("Location", $referer);
        } else {
            return $this->dispatcher->forward(array('controller' => 'applogin', 'action' => 'index'));
        }
    }
}
