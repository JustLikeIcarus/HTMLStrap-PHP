<?php

	/*
	 * Formstrap PHP
	 * Easily create bootstrap forms and form elements
	 * obviously best with Twitter Bootstrap, but you could use it without
	 *
	 * Created by the same guys who built Social Blendr (www.socialblendr.com)
	 * Copyright (c) 2012 WilliGant Ltd.
	 *
	 * founders@socialblendr.com
	 *
	 *
	 * Permission is hereby granted, free of charge, to any person obtaining a copy
	 * of this software and associated documentation files (the "Software"), to deal
	 * in the Software without restriction, including without limitation the rights
	 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	 * copies of the Software, and to permit persons to whom the Software is
	 * furnished to do so, subject to the following conditions:
	 *
	 * The above copyright notice and this permission notice shall be included in
	 * all copies or substantial portions of the Software.
	 *
	 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
	 * THE SOFTWARE.
	 *
	 * Inspired by the Nibble forms library
	 * Copyright (c) 2010 Luke Rotherfield, Nibble Development
	 *
	 */

	namespace formstrap;

		//WWBN allow different enctypes
	//WBN create quick forms like "search box" or "email_pw_login" w/ different options

	class form
	{

		private $form_action, $form_method, $form_class;
		private $fields;
		private $token_session_name;

		/*
		 * Constructor
		 * @oa	Will
		 *
		 * action  		where the form will post
		 * method 		POST or GET
		 * form_style	vertical, inline, search, horizontal (per twitter bootstrap)
		 * well			some styling on the form
		 *
		 */
		public function __construct($action = '', $method = 'post', $form_style = 'vertical', $well = FALSE)
		{
			$this->fields = new \stdClass();

			if ($well) {
				$form_style .= ' well';
			}
			$this->form_class  = $form_style;
			$this->form_method = strtoupper($method);
		}

		/*
		 * Magic functions
		 * @oa	Will
		 *
		 */
		public function __set($name, $value)
		{
			$this->fields->$name = $value;
		}

		public function  __get($name)
		{
			return $this->fields->$name;
		}

		/*
		 * Render
		 * @oa Will
		 *
		 * returns the HTML of the form
		 *
		 */
		public function render()
		{

			$token = $this->crsf_token();
			$html  = '<form class="' . $this->form_class . '" method="' . $this->form_method . '" enctype="enctype/form-data">';
			$html .= '<input type="hidden" value="' . $token . '" />';

			foreach ($this->fields as $name => $element) {
				if (is_object($element)) {
					$element->element_name($name);
					$html .= $element->render($name);
				} elseif (is_string($element)) {
					$html .= $element;
				}
			}

			return $html;
		}

		/*
		 * Create token session
		 * @oa Will
		 *
		 * //WTD check if the session is started
		 *
		 */
		private function crsf_token($length = 20)
		{
			$code  = "";
			$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
			srand((double)microtime() * 1000000);
			for ($i = 0; $i < $length; $i++) {
				$code .= substr($chars, rand() % strlen($chars), 1);
			}

			$_SESSION[$this->token_session_name] = $code;

			return $code;
		}

		/*
		 * Validate token session
		 * @oa	Will
		 *
		 * //WTD check if the session is started
		 *
		 */
		public function is_token_valid()
		{
			if ($this->form_method === 'POST') {
				if ($_POST[$this->token_session_name] == $_SESSION[$this->token_session_name]) {
					return TRUE;
				} else {
					return FALSE;
				}
			} else {
				if ($_GET[$this->token_session_name] == $_SESSION[$this->token_session_name]) {
					return TRUE;
				} else {
					return FALSE;
				}
			}
		}

	}

	/*
	 * All form elements
	 * @oa Will
	 *
	 *	//WBN add mouseevents / handlers
	 *
	 */
	abstract class element
	{
		public $class, $style, $tabindex, $id;
		protected $element_attributes = '';
		protected $element_name;

		protected $label;
		public $label_id = FALSE;
		public $label_stlye = FALSE;
		public $label_class = FALSE;

		protected $html = '';
		protected $disabled = FALSE;

		/*
		 * Set input name
		 * @oa	Will
		 *
		 */
		public function element_name($name) {
			$this->element_name = $name;
		}

		/*
		   * Create label
		   * @oa	Will
		   */
		protected function label()
		{
			$label_attributes = ' for="'.$this->element_name.'"';

			if ($this->label_class) {
				$label_attributes .= ' class="' . $this->label_class . '"';
			}

			$html = "<label$label_attributes>$this->label</label>";
			return $html;
		}

		/*
		 * Render
		 * @oa	Will
		 *
		 *  prepare the standard attributes for the element
		 *
		 */
		function render($name)
		{

			if ($this->class) {
				$this->element_attributes .= ' class="' . $this->class . '"';
			}
			if ($this->style) {
				$this->element_attributes .= ' style="' . $this->style . '"';
			}
			if ($this->id) {
				$this->element_attributes .= ' id="' . $this->id . '"';
			}
			if ($this->tabindex) {
				$this->element_attributes .= ' tabindex="' . $this->tabindex . '"';
			}

		}


	}

	class text extends element
	{
		protected $max_length, $value;
		protected $field_type = 'text';


		/*
		   * Construct
		   * @oa	Will
		   */
		public function __construct($label, $max_length = FALSE, $value = FALSE)
		{
			$this->label      = $label;
			$this->max_length = $max_length;
			$this->value      = $value;
		}

		/*
		 * Render
		 * @oa	Will
		 *
		 */
		public function render($name)
		{
			parent::render($name);

			if ($this->label) {
				$this->html = $this->label();
			}

			if ($this->value) {
				$this->element_attributes .= ' value= "' . $this->value . '"';
			}
			if ($this->max_length) {
				$this->element_attributes .= ' maxlength= "' . $this->max_length . '"';
			}
			if ($this->disabled) {
				$this->element_attributes .= ' disabled="disabled"';
			}

			$this->html .= '<input name="' . $name . '" type="' . $this->field_type . '" ' . $this->element_attributes . ' />';

			return $this->html;

		}

	}

		/*
	 * Hidden Elements
	 * @oa	Will
	 *
	 */
	class hidden extends text
	{

		/*
		   * Construct
		   * @oa	Will
		   */
		public function __construct($value)
		{
			$this->value      = $value;
			$this->field_type = 'hidden';
		}
	}

	/*
	 * Password Elements
	 * @oa	Will
	 *
	 */
	class password extends text
	{

		/*
		   * Construct
		   * @oa	Will
		   */
		public function __construct($label, $max_length = FALSE, $value = FALSE)
		{
			parent::__construct($label,$max_length,$value);
			$this->field_type = 'password';
		}
	}