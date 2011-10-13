<?php
/**
 * Add extension that can be added to an object with {@link Object::add_extension()}.
 * For {@link DataObject} extensions, use {@link DataObjectDecorator}.
 * Each extension instance has an "owner" instance, accessible through
 * {@link getOwner()}.
 * Every object instance gets its own set of extension instances,
 * meaning you can set parameters specific to the "owner instance"
 * in new Extension instances.
 *
 * @package sapphire
 * @subpackage core
 */
abstract class Extension {
	/**
	 * This is used by extensions designed to be applied to controllers.
	 * It works the same way as {@link Controller::$allowed_actions}.
	 */
	public static $allowed_actions = null;

	/**
	 * The DataObject that owns this decorator.
	 * @var DataObject
	 */
	protected $owner;
	
	/**
	 * The base class that this extension was applied to; $this->owner must be one of these
	 * @var DataObject
	 */
	protected $ownerBaseClass;
	
	/**
	 * Reference counter to ensure that the owner isn't cleared until clearOwner() has
	 * been called as many times as setOwner()
	 */
	private $ownerRefs = 0;
	
	public $class;
	
	function __construct() {
		$this->class = get_class($this);
	}

	/**
	 * Set the owner of this decorator.
	 * @param Object $owner The owner object,
	 * @param string $ownerBaseClass The base class that the extension is applied to; this may be
	 * the class of owner, or it may be a parent.  For example, if Versioned was applied to SiteTree,
	 * and then a Page object was instantiated, $owner would be a Page object, but $ownerBaseClass
	 * would be 'SiteTree'.
	 */
	function setOwner($owner, $ownerBaseClass = null) {
		if($owner) $this->ownerRefs++;
		$this->owner = $owner;

		if($ownerBaseClass) $this->ownerBaseClass = $ownerBaseClass;
		else if(!$this->ownerBaseClass && $owner) $this->ownerBaseClass = $owner->class;
	}
	
	function clearOwner() {
		if($this->ownerRefs <= 0) user_error("clearOwner() called more than setOwner()", E_USER_WARNING);
		$this->ownerRefs--;
		if($this->ownerRefs == 0) $this->owner = null;
	}
	
	/**
	 * Returns the owner of this decorator
	 *
	 * @return Object
	 */
	public function getOwner() {
		return $this->owner;
	}
	
	/**
	 * Helper method to strip eval'ed arguments from a string
	 * thats passed to {@link DataObject::$extensions} or 
	 * {@link Object::add_extension()}.
	 * 
	 * @param string $extensionStr E.g. "Versioned('Stage','Live')"
	 * @return string Extension classname, e.g. "Versioned"
	 */
	public static function get_classname_without_arguments($extensionStr) {
		return (($p = strpos($extensionStr, '(')) !== false) ? substr($extensionStr, 0, $p) : $extensionStr;
	}
	
	/**
	 * @see Object::get_static()
	 */
	public function stat($name, $uncached = false) {
		return Object::get_static(($this->class ? $this->class : get_class($this)), $name, $uncached);
	}
	
	/**
	 * @see Object::set_static()
	 */
	public function set_stat($name, $value) {
		Object::set_static(($this->class ? $this->class : get_class($this)), $name, $value);
	}
	
	/**
	 * @see Object::uninherited_static()
	 */
	public function uninherited($name) {
		return Object::uninherited_static(($this->class ? $this->class : get_class($this)), $name);
	}
	
}

?>