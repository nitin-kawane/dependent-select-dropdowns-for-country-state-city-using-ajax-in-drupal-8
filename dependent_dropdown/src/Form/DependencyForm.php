<?php
/**
 * @file
 * Contains \Drupal\dependent_dropdown\Form\DependencyForm
 */
namespace Drupal\dependent_dropdown\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;

class DependencyForm extends FormBase{
	public $connection;
	public function __construct(){
		$this->connection = \Drupal::database();
	}
	/**
	 * {@inheritdoc}
	 */
	public function getFormId(){
		return 'dependent_form';
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$countries = $this->getCountries();
		$form['country'] = [
			'#type' => 'select',
			'#title' => t('Country'),
			'#required' => TRUE,
			'#options' => $countries,
			'#ajax' => [
				'callback' => [$this, 'getStates'],
				'disable-refocus' => FALSE,
				'event' => 'change',
				'wrapper' => 'state-wrapper',
				'progress' => 'throbber',
			],
		];
		
		$form['state'] = [
			'#type' => 'select',
			'#title' => t('State'),
			'#required' => TRUE,
			'#options' => array(),
			'#validated' => true,
			'#attributes' => array('class' => array('state_select')),
			'#ajax' => [
				'callback' => [$this, 'getCities'],
				'disable-refocus' => FALSE,
				'event' => 'change',
				'wrapper' => 'city-wrapper',
				'progress' => 'throbber',
			],
			
		];
		
		$form['city'] = [
			'#type' => 'select',
			'#title' => t('City'),
			'#required' => TRUE,
			'#validated' => true,
			'#options' => array(),
			'#attributes' => array('class' => array('city_select')),
		];
		
		return $form;
	}	
	
	/**
	 * {@inheritdoc}
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		
	}
	
	/**
	 * Get countries from DB
	 */
	public function getCountries(){
		$query = $this->connection->query("SELECT countryid, country FROM country");
		$result = $query->fetchAll();
		$countryArray = array();
		foreach($result as $key=>$val){
			$countryArray[$val->countryid] = $val->country;
		}
		return $countryArray;
	}
	
	/**
	 * Get states from DB
	 */
	public function getStates(array &$element, FormStateInterface $form_state){
		$response = new AjaxResponse();
		$countryValue = $form_state->getValue('country');
		if($countryValue!=null){
			$query = $this->connection->query("SELECT id, statename FROM state WHERE countryid=".$countryValue);
			$result = $query->fetchAll();
			$state_options = "<option value>-Select State-</option>";
			if(!empty($result)){
				foreach ($result as $key => $value) {
				   $state_options .= "<option value='".$value->id."'>" . $value->statename . "</option>";
				}
			}
			$response->addCommand(new HtmlCommand('select.state_select', $state_options));
			$response->addCommand(new HtmlCommand('select.city_select', '<option value>-Select City-</option>'));
		}
		return $response;
	}
	
	/**
	 * Get cities from DB
	 */
	public function getCities(array &$element, FormStateInterface $form_state){
		$response = new AjaxResponse();
		$stateValue = $form_state->getValue('state');
		if($stateValue!=null){
			$query = $this->connection->query("SELECT id, city FROM city WHERE stateid=".$stateValue);
			$result = $query->fetchAll();
			$city_options = "<option value>-Select City-</option>";
			if(!empty($result)){
				foreach ($result as $key => $value) {
				   $city_options .= "<option value='".$value->id."'>" . $value->city . "</option>";
				}
			}
			$response->addCommand(new HtmlCommand('select.city_select', $city_options));
		}
		return $response;
	}

} 
