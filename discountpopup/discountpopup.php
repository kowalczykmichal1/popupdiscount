<?php
if (!defined('_PS_VERSION_'))
  exit;

require_once dirname(__FILE__) . '/models/WPModel.php';

class DiscountPopup extends Module
{
	/* GENERAL INFORMATION ABOUT MODULE */
	
	public function __construct()
	{
		$this->name = 'discountpopup';
		$this->tab = 'others';
		$this->version = '1.0.0';
		$this->author = 'Michal Kowalczyk';
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_); 
		$this->bootstrap = true;
	 
		parent::__construct();
	 
		$this->displayName = $this->l('Popup z informacją o zniżce');
		$this->description = $this->l('Popup z informacją o zniżce');
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
	 
	}
	
	/* INSTALL MODULE FUNCTION */
	public function install()
	{		
		/* REGISTER HOOK Header AND SET DEFAULT VALUE FOR POPUP*/
		if (!parent::install() ||
			!$this->registerHook('Header') ||
			!Configuration::updateValue('dptext', '<p class="discount-popup-title"><strong>Wait!</strong></p><p class="discount-popup-subtitle">We want to give you <strong>15% discount</strong> for your first order!</p><p class="discount-popup-subtitle"><strong>Use the discount code</strong> at the checkout  - BH10</p>', true)
		)
		return false;
		
		return true;
	}
	
	/* UNINSTALL FUNCTION */
	public function uninstall() {
		
		/* REMOVE DEFAULT POPUP VALUE*/
        Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . 'configuration` WHERE `name` LIKE "dptext"');
        
		/* UNINSTALL AND REMOVE HEADER HOOK */
        if (!parent::uninstall() || !$this->unregisterHook('displayHeader'))
            return false;
        return true;

    }
	
	/* GET CONTENT FOR POPUP CONFIGURATION*/
	public function getContent()
	{
		$output = null;
	 
		if (Tools::isSubmit('submit'.$this->name))
		{
			$dpvalue = Tools::getValue('dptext');
			
			if (!$dpvalue
			  || empty($dpvalue))
				$output .= $this->displayError($this->l('Niepoprawna wartość.'));
			else
			{
				Configuration::updateValue('dptext', $dpvalue, true);
				$output .= $this->displayConfirmation($this->l('Zaktualizowano!'));
			}
		}
		return $output.$this->displayForm();
	}
	
	/* DISPLAY CONFIGURATION FORM */
	public function displayForm()
	{
		
		$default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
		 
		/* CREATE FIELDS */
		
		$fields_form[0]['form'] = array(
			'legend' => array(
				'title' => $this->l('Ustawienia'),
			),
			'input' => array(
				array(
					'type' => 'textarea',
					'label' => $this->l('Wyświetlany tekst'),
					'name' => 'dptext',
					'autoload_rte' => true,
					'rows' => 10,
                    'cols' => 100,
					'required' => true
				)
			),
			'submit' => array(
				'title' => $this->l('Zapisz'),
				'class' => 'btn btn-default pull-right'
			)
		);
		
		/* HELPER FORM */
		$helper = new HelperForm();
		 
		
		$helper->module = $this;
		$helper->name_controller = $this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
		 
		
		$helper->default_form_language = $default_lang;
		$helper->allow_employee_form_lang = $default_lang;
		 
	
		$helper->title = $this->displayName;
		$helper->show_toolbar = true; 
		$helper->toolbar_scroll = true; 
		$helper->submit_action = 'submit'.$this->name;
		$helper->toolbar_btn = array(
			'save' =>
			array(
				'desc' => $this->l('Save'),
				'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
				'&token='.Tools::getAdminTokenLite('AdminModules'),
			),
			'back' => array(
				'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
				'desc' => $this->l('Back to list')
			)
		);
		 
		
		/* SET DEFAULT VALUE FOR INPUT*/
		$helper->fields_value['dptext'] = Configuration::get('dptext');
		 
		return $helper->generateForm($fields_form);
	}

	/* HOOK HEADER FUNCTION */
	public function hookHeader(){
		$output = null;
		
		/* GET POPUP CONTENT */
		$show_text = Configuration::get('dptext');
		
		/* ADD JS AND CSS SCRIPTS */
		$this->context->controller->registerJavascript('modules-discountpopup', 'modules/'.$this->name.'/views/js/discountpopup.js', ['position' => 'bottom', 'priority' => 150]);
		$this->context->controller->addCSS( $this->_path . 'views/css/discountpopup.css');
		
		/* SEND VARIABLE TO TEMPLATE */
		$this->context->smarty->assign('show_text', $show_text);
		
		
		/* DISPLAY TEMPLATE */
		return $this->display(__FILE__, 'index.tpl');
	}
	
	
}