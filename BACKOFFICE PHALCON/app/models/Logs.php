<?php
/**
 * Auteur: SOBANDITH Jimmy
 * Société: Warmbee (Deliv's SARL)
 * Date: 2016
 * Desc: Modèle de données de la table des logs
 */
class Logs extends \Phalcon\Mvc\Model
{
    public $id;
	public $id_utilisateur;
	public $projet;
	public $date;
	public $controller;
	public $action;
	public $uri;

    /**
     * Mapping du modèle avec la table de la base de données
     */
    public function getSource()
    {
        return 'logs';
    }
	
	public function initialize()
    {
		$this->belongsTo('id_utilisateur', 'Utilisateurs', 'id');
    }
}
?>