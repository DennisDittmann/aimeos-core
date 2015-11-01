<?php

/**
 * @copyright Copyright (c) Metaways Infosystems GmbH, 2013
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @package Client
 * @subpackage Html
 */


/**
 * Default implementation of catalog stage section HTML clients.
 *
 * @package Client
 * @subpackage Html
 */
class Client_Html_Catalog_Stage_Default
	extends Client_Html_Common_Client_Factory_Abstract
	implements Client_Html_Common_Client_Factory_Interface
{
	/** client/html/catalog/stage/default/subparts
	 * List of HTML sub-clients rendered within the catalog stage section
	 *
	 * The output of the frontend is composed of the code generated by the HTML
	 * clients. Each HTML client can consist of serveral (or none) sub-clients
	 * that are responsible for rendering certain sub-parts of the output. The
	 * sub-clients can contain HTML clients themselves and therefore a
	 * hierarchical tree of HTML clients is composed. Each HTML client creates
	 * the output that is placed inside the container of its parent.
	 *
	 * At first, always the HTML code generated by the parent is printed, then
	 * the HTML code of its sub-clients. The order of the HTML sub-clients
	 * determines the order of the output of these sub-clients inside the parent
	 * container. If the configured list of clients is
	 *
	 *  array( "subclient1", "subclient2" )
	 *
	 * you can easily change the order of the output by reordering the subparts:
	 *
	 *  client/html/<clients>/subparts = array( "subclient1", "subclient2" )
	 *
	 * You can also remove one or more parts if they shouldn't be rendered:
	 *
	 *  client/html/<clients>/subparts = array( "subclient1" )
	 *
	 * As the clients only generates structural HTML, the layout defined via CSS
	 * should support adding, removing or reordering content by a fluid like
	 * design.
	 *
	 * @param array List of sub-client names
	 * @since 2014.03
	 * @category Developer
	 */
	private $_subPartPath = 'client/html/catalog/stage/default/subparts';

	/** client/html/catalog/stage/image/name
	 * Name of the image part used by the catalog stage client implementation
	 *
	 * Use "Myname" if your class is named "Client_Html_Catalog_Stage_Image_Myname".
	 * The name is case-sensitive and you should avoid camel case names like "MyName".
	 *
	 * @param string Last part of the client class name
	 * @since 2014.03
	 * @category Developer
	 */

	/** client/html/catalog/stage/breadcrumb/name
	 * Name of the breadcrumb part used by the catalog stage client implementation
	 *
	 * Use "Myname" if your class is named "Client_Html_Catalog_Stage_Breadcrumb_Myname".
	 * The name is case-sensitive and you should avoid camel case names like "MyName".
	 *
	 * @param string Last part of the client class name
	 * @since 2014.03
	 * @category Developer
	 */

	/** client/html/catalog/stage/navigator/name
	 * Name of the navigator part used by the catalog stage client implementation
	 *
	 * Use "Myname" if your class is named "Client_Html_Catalog_Stage_Breadcrumb_Myname".
	 * The name is case-sensitive and you should avoid camel case names like "MyName".
	 *
	 * @param string Last part of the client class name
	 * @since 2014.09
	 * @category Developer
	 */
	private $_subPartNames = array( 'image', 'breadcrumb', 'navigator' );

	private $_tags = array();
	private $_expire;
	private $_params;
	private $_cache;


	/**
	 * Returns the HTML code for insertion into the body.
	 *
	 * @param string $uid Unique identifier for the output if the content is placed more than once on the same page
	 * @param array &$tags Result array for the list of tags that are associated to the output
	 * @param string|null &$expire Result variable for the expiration date of the output (null for no expiry)
	 * @return string HTML code
	 */
	public function getBody( $uid = '', array &$tags = array(), &$expire = null )
	{
		$prefixes = array( 'f' );

		/** client/html/catalog/stage
		 * All parameters defined for the catalog stage component and its subparts
		 *
		 * This returns all settings related to the stage component.
		 * Please refer to the single settings for details.
		 *
		 * @param array Associative list of name/value settings
		 * @category Developer
		 * @see client/html/catalog#stage
		 */
		$confkey = 'client/html/catalog/stage';

		if( ( $html = $this->_getCached( 'body', $uid, $prefixes, $confkey ) ) === null )
		{
			$context = $this->_getContext();
			$view = $this->getView();

			try
			{
				$view = $this->_setViewParams( $view, $tags, $expire );

				$output = '';
				foreach( $this->_getSubClients() as $subclient ) {
					$output .= $subclient->setView( $view )->getBody( $uid, $tags, $expire );
				}
				$view->stageBody = $output;
			}
			catch( Client_Html_Exception $e )
			{
				$error = array( $context->getI18n()->dt( 'client/html', $e->getMessage() ) );
				$view->stageErrorList = $view->get( 'stageErrorList', array() ) + $error;
			}
			catch( Controller_Frontend_Exception $e )
			{
				$error = array( $context->getI18n()->dt( 'controller/frontend', $e->getMessage() ) );
				$view->stageErrorList = $view->get( 'stageErrorList', array() ) + $error;
			}
			catch( MShop_Exception $e )
			{
				$error = array( $context->getI18n()->dt( 'mshop', $e->getMessage() ) );
				$view->stageErrorList = $view->get( 'stageErrorList', array() ) + $error;
			}
			catch( Exception $e )
			{
				$context->getLogger()->log( $e->getMessage() . PHP_EOL . $e->getTraceAsString() );

				$error = array( $context->getI18n()->dt( 'client/html', 'A non-recoverable error occured' ) );
				$view->stageErrorList = $view->get( 'stageErrorList', array() ) + $error;
			}

			/** client/html/catalog/stage/default/template-body
			 * Relative path to the HTML body template of the catalog stage client.
			 *
			 * The template file contains the HTML code and processing instructions
			 * to generate the result shown in the body of the frontend. The
			 * configuration string is the path to the template file relative
			 * to the layouts directory (usually in client/html/layouts).
			 *
			 * You can overwrite the template file configuration in extensions and
			 * provide alternative templates. These alternative templates should be
			 * named like the default one but with the string "default" replaced by
			 * an unique name. You may use the name of your project for this. If
			 * you've implemented an alternative client class as well, "default"
			 * should be replaced by the name of the new class.
			 *
			 * @param string Relative path to the template creating code for the HTML page body
			 * @since 2014.03
			 * @category Developer
			 * @see client/html/catalog/stage/default/template-header
			 */
			$tplconf = 'client/html/catalog/stage/default/template-body';
			$default = 'catalog/stage/body-default.html';

			$html = $view->render( $this->_getTemplate( $tplconf, $default ) );

			$this->_setCached( 'body', $uid, $prefixes, $confkey, $html, $tags, $expire );
		}
		else
		{
			$html = $this->modifyBody( $html, $uid );
		}

		return $html;
	}


	/**
	 * Returns the HTML string for insertion into the header.
	 *
	 * @param string $uid Unique identifier for the output if the content is placed more than once on the same page
	 * @param array &$tags Result array for the list of tags that are associated to the output
	 * @param string|null &$expire Result variable for the expiration date of the output (null for no expiry)
	 * @return string|null String including HTML tags for the header on error
	 */
	public function getHeader( $uid = '', array &$tags = array(), &$expire = null )
	{
		$prefixes = array( 'f' );
		$confkey = 'client/html/catalog/stage';

		if( ( $html = $this->_getCached( 'header', $uid, $prefixes, $confkey ) ) === null )
		{
			$view = $this->getView();

			try
			{
				$view = $this->_setViewParams( $view, $tags, $expire );

				$output = '';
				foreach( $this->_getSubClients() as $subclient ) {
					$output .= $subclient->setView( $view )->getHeader( $uid, $tags, $expire );
				}
				$view->stageHeader = $html;

				/** client/html/catalog/stage/default/template-header
				 * Relative path to the HTML header template of the catalog stage client.
				 *
				 * The template file contains the HTML code and processing instructions
				 * to generate the HTML code that is inserted into the HTML page header
				 * of the rendered page in the frontend. The configuration string is the
				 * path to the template file relative to the layouts directory (usually
				 * in client/html/layouts).
				 *
				 * You can overwrite the template file configuration in extensions and
				 * provide alternative templates. These alternative templates should be
				 * named like the default one but with the string "default" replaced by
				 * an unique name. You may use the name of your project for this. If
				 * you've implemented an alternative client class as well, "default"
				 * should be replaced by the name of the new class.
				 *
				 * @param string Relative path to the template creating code for the HTML page head
				 * @since 2014.03
				 * @category Developer
				 * @see client/html/catalog/stage/default/template-body
				 */
				$tplconf = 'client/html/catalog/stage/default/template-header';
				$default = 'catalog/stage/header-default.html';

				$html = $view->render( $this->_getTemplate( $tplconf, $default ) );

				$this->_setCached( 'header', $uid, $prefixes, $confkey, $html, $tags, $expire );
			}
			catch( Exception $e )
			{
				$this->_getContext()->getLogger()->log( $e->getMessage() . PHP_EOL . $e->getTraceAsString() );
			}
		}
		else
		{
			$html = $this->modifyHeader( $html, $uid );
		}

		return $html;
	}


	/**
	 * Returns the sub-client given by its name.
	 *
	 * @param string $type Name of the client type
	 * @param string|null $name Name of the sub-client (Default if null)
	 * @return Client_Html_Interface Sub-client object
	 */
	public function getSubClient( $type, $name = null )
	{
		/** client/html/catalog/stage/decorators/excludes
		 * Excludes decorators added by the "common" option from the catalog stage html client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to remove a decorator added via
		 * "client/html/common/decorators/default" before they are wrapped
		 * around the html client.
		 *
		 *  client/html/catalog/stage/decorators/excludes = array( 'decorator1' )
		 *
		 * This would remove the decorator named "decorator1" from the list of
		 * common decorators ("Client_Html_Common_Decorator_*") added via
		 * "client/html/common/decorators/default" to the html client.
		 *
		 * @param array List of decorator names
		 * @since 2014.05
		 * @category Developer
		 * @see client/html/common/decorators/default
		 * @see client/html/catalog/stage/decorators/global
		 * @see client/html/catalog/stage/decorators/local
		 */

		/** client/html/catalog/stage/decorators/global
		 * Adds a list of globally available decorators only to the catalog stage html client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap global decorators
		 * ("Client_Html_Common_Decorator_*") around the html client.
		 *
		 *  client/html/catalog/stage/decorators/global = array( 'decorator1' )
		 *
		 * This would add the decorator named "decorator1" defined by
		 * "Client_Html_Common_Decorator_Decorator1" only to the html client.
		 *
		 * @param array List of decorator names
		 * @since 2014.05
		 * @category Developer
		 * @see client/html/common/decorators/default
		 * @see client/html/catalog/stage/decorators/excludes
		 * @see client/html/catalog/stage/decorators/local
		 */

		/** client/html/catalog/stage/decorators/local
		 * Adds a list of local decorators only to the catalog stage html client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap local decorators
		 * ("Client_Html_Catalog_Decorator_*") around the html client.
		 *
		 *  client/html/catalog/stage/decorators/local = array( 'decorator2' )
		 *
		 * This would add the decorator named "decorator2" defined by
		 * "Client_Html_Catalog_Decorator_Decorator2" only to the html client.
		 *
		 * @param array List of decorator names
		 * @since 2014.05
		 * @category Developer
		 * @see client/html/common/decorators/default
		 * @see client/html/catalog/stage/decorators/excludes
		 * @see client/html/catalog/stage/decorators/global
		 */
		return $this->_createSubClient( 'catalog/stage/' . $type, $name );
	}


	/**
	 * Processes the input, e.g. store given values.
	 * A view must be available and this method doesn't generate any output
	 * besides setting view variables.
	 */
	public function process()
	{
		$view = $this->getView();

		try
		{
			parent::process();
		}
		catch( Client_Html_Exception $e )
		{
			$error = array( $this->_getContext()->getI18n()->dt( 'client/html', $e->getMessage() ) );
			$view->stageErrorList = $view->get( 'stageErrorList', array() ) + $error;
		}
		catch( Controller_Frontend_Exception $e )
		{
			$error = array( $this->_getContext()->getI18n()->dt( 'controller/frontend', $e->getMessage() ) );
			$view->stageErrorList = $view->get( 'stageErrorList', array() ) + $error;
		}
		catch( MShop_Exception $e )
		{
			$error = array( $this->_getContext()->getI18n()->dt( 'mshop', $e->getMessage() ) );
			$view->stageErrorList = $view->get( 'stageErrorList', array() ) + $error;
		}
		catch( Exception $e )
		{
			$context = $this->_getContext();
			$context->getLogger()->log( $e->getMessage() . PHP_EOL . $e->getTraceAsString() );

			$error = array( $context->getI18n()->dt( 'client/html', 'A non-recoverable error occured' ) );
			$view->stageErrorList = $view->get( 'stageErrorList', array() ) + $error;
		}
	}


	/**
	 * Returns the parameters used by the html client.
	 *
	 * @param array $params Associative list of all parameters
	 * @param array $prefixes List of prefixes the parameters must start with
	 * @return array Associative list of parameters used by the html client
	 */
	protected function _getClientParams( array $params, array $prefixes = array( 'f', 'l', 'd', 'a' ) )
	{
		$list = parent::_getClientParams( $params, array_merge( $prefixes, array( 'l', 'd' ) ) );

		if( isset( $list['l_pos'] ) && isset( $list['d_prodid'] ) )
		{
			$context = $this->_getContext();
			$site = $context->getLocale()->getSite()->getCode();
			$list += (array) $context->getSession()->get( 'arcavias/catalog/list/params/last/' . $site, array() );
		}

		return $list;
	}


	/**
	 * Returns the list of sub-client names configured for the client.
	 *
	 * @return array List of HTML client names
	 */
	protected function _getSubClientNames()
	{
		return $this->_getContext()->getConfig()->get( $this->_subPartPath, $this->_subPartNames );
	}


	/**
	 * Sets the necessary parameter values in the view.
	 *
	 * @param MW_View_Interface $view The view object which generates the HTML output
	 * @param array &$tags Result array for the list of tags that are associated to the output
	 * @param string|null &$expire Result variable for the expiration date of the output (null for no expiry)
	 * @return MW_View_Interface Modified view object
	 */
	protected function _setViewParams( MW_View_Interface $view, array &$tags = array(), &$expire = null )
	{
		if( !isset( $this->_cache ) )
		{
			$params = $this->_getClientParams( $view->param(), array( 'f' ) );

			if( isset( $params['f_catid'] ) && $params['f_catid'] != '' )
			{
				$context = $this->_getContext();
				$config = $context->getConfig();
				$controller = Controller_Frontend_Factory::createController( $context, 'catalog' );

				$default = array( 'attribute', 'media', 'text' );

				/** client/html/catalog/domains
				 * A list of domain names whose items should be available in the catalog view templates
				 *
				 * @see client/html/catalog/stage/domains
				 */
				$domains = $config->get( 'client/html/catalog/domains', $default );

				/** client/html/catalog/stage/default/domains
				 * A list of domain names whose items should be available in the catalog stage view template
				 *
				 * The templates rendering the catalog stage section use the texts and
				 * maybe images and attributes associated to the categories. You can
				 * configure your own list of domains (attribute, media, price, product,
				 * text, etc. are domains) whose items are fetched from the storage.
				 * Please keep in mind that the more domains you add to the configuration,
				 * the more time is required for fetching the content!
				 *
				 * This configuration option overwrites the "client/html/catalog/domains"
				 * option that allows to configure the domain names of the items fetched
				 * for all catalog related data.
				 *
				 * @param array List of domain names
				 * @since 2014.03
				 * @category Developer
				 * @see client/html/catalog/domains
				 * @see client/html/catalog/detail/domains
				 * @see client/html/catalog/list/domains
				 */
				$domains = $config->get( 'client/html/catalog/stage/default/domains', $domains );
				$stageCatPath = $controller->getCatalogPath( $params['f_catid'], $domains );

				if( ( $categoryItem = end( $stageCatPath ) ) !== false ) {
					$view->stageCurrentCatItem = $categoryItem;
				}

				$this->_addMetaItem( $stageCatPath, 'catalog', $this->_expire, $this->_tags );
				$this->_addMetaList( array_keys( $stageCatPath ), 'catalog', $this->_expire );

				$view->stageCatPath = $stageCatPath;
			}

			$view->stageParams = $params;

			$this->_cache = $view;
		}

		$expire = $this->_expires( $this->_expire, $expire );
		$tags = array_merge( $tags, $this->_tags );

		return $this->_cache;
	}
}
