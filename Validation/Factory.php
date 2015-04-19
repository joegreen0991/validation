<?php namespace Validation;

use Closure;

class Factory {

	/**
	 * The message array.
	 *
	 * @var array
	 */
	protected $messages;

	/**
	 * The Presence Verifier implementation.
	 *
	 * @var \Validation\PresenceVerifierInterface
	 */
	protected $verifier;

	/**
	 * All of the custom validator extensions.
	 *
	 * @var array
	 */
	protected $extensions = array();

	/**
	 * All of the custom implicit validator extensions.
	 *
	 * @var array
	 */
	protected $implicitExtensions = array();

	/**
	 * All of the custom validator message replacers.
	 *
	 * @var array
	 */
	protected $replacers = array();

	/**
	 * All of the fallback messages for custom rules.
	 *
	 * @var array
	 */
	protected $fallbackMessages = array();

	/**
	 * Create a new Validator factory instance.
	 *
	 * @param array $messages
	 * @param PresenceVerifierInterface $presenceVerifier
	 */
	public function __construct(array $messages = null, PresenceVerifierInterface $presenceVerifier = null)
	{
		$this->messages = $messages ?: include __DIR__ . '/messages.php';
                
                $this->verifier = $presenceVerifier;
	}

	/**
	 * Create a new Validator instance.
	 *
	 * @param array $data
	 * @param array $rules
	 * @param array $customAttributes
	 * @return Validator
	 */
	public function make(array $data, array $rules, array $customAttributes = array())
	{
		// The presence verifier is responsible for checking the unique and exists data
		// for the validator. It is behind an interface so that multiple versions of
		// it may be written besides database. We'll inject it into the validator.
		$validator = new Validator($this->messages, $data, $rules, $customAttributes);

		if ( ! is_null($this->verifier))
		{
			$validator->setPresenceVerifier($this->verifier);
		}

		$this->addExtensions($validator);

		return $validator;
	}

	/**
	 * Add the extensions to a validator instance.
	 *
	 * @param $validator
	 */
	protected function addExtensions($validator)
	{
		$validator->addExtensions($this->extensions);

		// Next, we will add the implicit extensions, which are similar to the required
		// and accepted rule in that they are run even if the attributes is not in a
		// array of data that is given to a validator instances via instantiation.
		$implicit = $this->implicitExtensions;

		$validator->addImplicitExtensions($implicit);

		$validator->addReplacers($this->replacers);

		$validator->setFallbackMessages($this->fallbackMessages);
	}

	/**
	 * Register a custom validator extension.
	 *
	 * @param  string  $rule
	 * @param  \Closure|string  $extension
	 * @param  string  $message
	 * @return void
	 */
	public function extend($rule, $extension, $message = null)
	{
		$this->extensions[$rule] = $extension;

		if ($message) $this->fallbackMessages[snake_case($rule)] = $message;
	}

	/**
	 * Register a custom implicit validator extension.
	 *
	 * @param  string   $rule
	 * @param  \Closure|string  $extension
	 * @param  string  $message
	 * @return void
	 */
	public function extendImplicit($rule, $extension, $message = null)
	{
		$this->implicitExtensions[$rule] = $extension;

		if ($message) $this->fallbackMessages[snake_case($rule)] = $message;
	}

	/**
	 * Register a custom implicit validator message replacer.
	 *
	 * @param  string   $rule
	 * @param  \Closure|string  $replacer
	 * @return void
	 */
	public function replacer($rule, $replacer)
	{
		$this->replacers[$rule] = $replacer;
	}

	/**
	 * Get the Presence Verifier implementation.
	 *
	 * @return \Validation\PresenceVerifierInterface
	 */
	public function getPresenceVerifier()
	{
		return $this->verifier;
	}

	/**
	 * Set the Presence Verifier implementation.
	 *
	 * @param  \Validation\PresenceVerifierInterface  $presenceVerifier
	 * @return void
	 */
	public function setPresenceVerifier(PresenceVerifierInterface $presenceVerifier)
	{
		$this->verifier = $presenceVerifier;
	}

}
