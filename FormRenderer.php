<?php
if (!defined("__ALLOW_INCLUDE__")) {
		exit("Obsolete content confirmation Page");
	}

class FormRenderer {
	
	private $formName;
	private $formId;
	private $mainText;
	private $headerText;
	private $footerText;
	private $buttons;
	private $fields;
	private $submitIsSet;
	private $inputFieldPostfix;
	private $validFieldTypes;
	private $styleClasses;
	private $validStyleSources;
	private $validFieldParameters;
	private const BT_SUBMIT = "S";
	private const BT_OPTION = "O";
	private const URL = "U";
	private const LABEL = "L";
	private const TYPE = "T";
	//Field types
	public const FT_CHECKBOX = "checkbox";
	public const FT_COLOR = "color";
	public const FT_DATE = "date";
	public const FT_DATETIME_LOCAL = "datetime-local";
	public const FT_EMAIL = "email";
	public const FT_FILE = "file";
	public const FT_HIDDEN = "hidden";
	public const FT_IMAGE = "image";
	public const FT_MONTH = "month";
	public const FT_NUMBER = "number";
	public const FT_PASSWORD = "password";
	public const FT_RADIO = "radio";
	public const FT_SEARCH = "search";
	public const FT_TEXT = "text";
	public const FT_TIME = "time";
	public const FT_TEXTAREA = "textarea";
	//String expression to che the input
	private const STRING_EXPRESSION = '/^[a-zA-Z0-9öÖüÜóÓőŐúÚéÉáÁűŰíÍäÄ]+$/i';
	private const SENTENCE_EXPRESSION = '/^[a-zA-Z0-9öÖüÜóÓőŐúÚéÉáÁűŰíÍäÄ\.,:\"\'-_@\[\]&()–!?]+$/i';
	private const STYLESET_EXPRESSION = '/^[a-zA-Z0-9_\-\s]+$/i';
	//style constants
	public const STYLE_FORM = "form";
	public const STYLE_FORM_HEADER = "header";
	public const STYLE_MAIN_TEXT = "mt";
	public const STYLE_FIELD_AREA = "fields";
	public const STYLE_BUTTON_AREA = "buttons";
	public const STYLE_FORM_FOOTER = "footer";
	public const FIELD_POSTFIX_SEPARATOR = "-";
	//field parameters
	public const FP_VALUE = "value";
	public const FP_SIZE = "size";
	public const FP_TITLE = "title";
	public const FP_READONLY = "readonly";


	public function __construct($formName, $formId = null) {

		$name = $this->checkValidId($formName, "Form Name"); 
		if (isset($formId)) {
			$formId = trim($formId);
			$formId = str_replace(' ', '', $formId);
			if (!ctype_alnum($formId)) {
				throw new Exception("Form id is invalid!");
			}
			$this->formId = htmlspecialchars($formId, ENT_QUOTES);
		}

		$this->formName = htmlspecialchars($formName, ENT_QUOTES);
		$this->buttons = array();
		$this->fields = array();
		$this->styleClasses = array();
		$this->submitIsSet = false;
		$this->validFieldTypes = [
			self::FT_CHECKBOX,
			self::FT_COLOR,
			self::FT_DATE,
			self::FT_DATETIME_LOCAL,
			self::FT_EMAIL,
			self::FT_FILE,
			self::FT_HIDDEN,
			self::FT_IMAGE,
			self::FT_MONTH,
			self::FT_NUMBER,
			self::FT_PASSWORD,
			self::FT_RADIO,
			self::FT_SEARCH,
			self::FT_TEXT,
			self::FT_TIME,
			self::FT_TEXTAREA
		];
		$this->validStyleSources = [
			self::STYLE_FORM,
			self::STYLE_FORM_HEADER,
			self::STYLE_MAIN_TEXT,
			self::STYLE_FIELD_AREA,
			self::STYLE_BUTTON_AREA,
			self::STYLE_FORM_FOOTER
		];
		$this->validFieldParameters = [
			self::FP_TITLE,
			self::FP_SIZE,
			self::FP_VALUE,
			self::FP_READONLY

		];
	}

	public function setMainText($mainText, bool $containsHTML = null ) {
		if (isset($this->mainText)) {
			throw new Exception("Main text is already set!");
		}
		$mainText = $this->checkValidSentences($mainText, "Main text");
		if (isset($containsHTML) && $containsHTML) {
			$this->mainText = $mainText;
		} else {
			$this->mainText = htmlspecialchars($mainText, ENT_QUOTES);
		}

	}
	public function setHeaderText($headerText) {
		if (isset($this->headerText)) {
			throw new Exception("Header text is already set!");
		}
		$headerText = $this->checkValidString($headerText, "Header text"); 
		$this->headerText = htmlspecialchars($headerText, ENT_QUOTES);
	}
	public function setFooterText($footerText, bool $containsHTML = null) {
		if (isset($this->footerText)) {
			throw new Exception("Footer text is already set!");
		}
		
		$footerText = $this->checkValidSentences($footerText, "Footer text"); 
		
		if (isset($containsHTML) && $containsHTML) {
			$this->footerText = $footerText;
		} else {
			$this->footerText = htmlspecialchars($footerText, ENT_QUOTES);
		}
		
	}

	public function addField($label, $name, $type, $optionalParameters = null) {
		
		$label = $this->checkValidString($label, "Label"); 
		
		$name = $this->checkValidId($name, "Name"); 

		if (!isset($type)) {
			throw new Exception("Type is misisng!");
		}
		$internalName = htmlspecialchars($name, ENT_QUOTES);
		$internalLabel = htmlspecialchars($label, ENT_QUOTES);
		$internalValue = "";
		$size = null;
		$title = "";
		$readonly = false;
		if (isset($optionalParameters)) {
			if (isset($optionalParameters[self::FP_VALUE])) {
				$value = trim($optionalParameters[self::FP_VALUE]);
				$internalValue = htmlspecialchars($value, ENT_QUOTES);
			}
			if (isset($optionalParameters[self::FP_TITLE])) {
				$value = trim($optionalParameters[self::FP_TITLE]);
				$title = htmlspecialchars($value, ENT_QUOTES);
			}
			if (isset($optionalParameters[self::FP_SIZE])) {
				$value = trim($optionalParameters[self::FP_SIZE]);
				
				if (!is_numeric($value)) {
					
					throw new Exception("Invalid size parameter: " . $value);
				}
				$size = $value;
			}
			if (isset($optionalParameters[self::FP_READONLY])) {
				$value = (bool) trim($optionalParameters[self::FP_READONLY]);
				//if ($value != true || $value != false) {
				//	throw new Exception("Invalid readonly parameter: " . print_r($value, true));
				//}
				$readonly = $value;
			}
		}

		
		if (array_key_exists($internalName, $this->fields)) {
			throw new Exception("Field has already added to the set!");
		}
		if (!in_array($type, $this->validFieldTypes)) {
			throw new Exception("Invalid field type!");
		}
		array_push($this->fields, [$internalName => [$internalLabel, $type, $internalValue, $size, $title, $readonly]]);
	}

	//This string is added to the non-button type input fields as an extra security feature.
	//With this it can be check, whether the post request belongs to the actual user.
	public function setInputFileldPostfix($postfix) {
		if (isset($this->inputFieldPostfix)) {
			throw new Exception("Input Field Postfix is already set!");
		}
		if (!ctype_alnum($postfix)) {
			throw new Exception("Postfix must be alfanumerical!");
		}
		$this->inputFieldPostfix = $postfix;
	}

	public function setSubmitButton($label, $url) {
		if ($this->submitIsSet) {
			throw new Exception("Submit data is already set!");
		}
		
		$this->addOptionImpl($label, $url, self::BT_SUBMIT);
		$this->submitIsSet = true;
	}
	public function addButton($label, $url) {
		$this->addOptionImpl($label, $url, self::BT_OPTION);
	}
	private function addOptionImpl ($label, $url, $type) {
		
		$label = $this->checkValidString($label, "Label"); 
		if (!isset($url) || strlen($url) === 0) {
			throw new Exception("URL is missing");
		}
		$button = [self::LABEL => htmlspecialchars($label, ENT_QUOTES),
					self::URL =>  htmlspecialchars($url, ENT_QUOTES),
					self::TYPE => $type];
		array_push($this->buttons, $button);
	}

	public function setStyles($stylesArray) {

		if (!isset($stylesArray)) {
			throw new Exception("Styles array is missing");
		}
		if (!is_array($stylesArray)) {
			throw new Exception("Function parameter must be an array");
		}
		if (empty($stylesArray)) {
			throw new Exception("Styles array is empty!");
		}

		foreach ($stylesArray as $key => $value) {

			if (!isset($key) || is_numeric($key)) {
				throw new Exception("Style array has wrong format. <<Style source>> => <<classes>> format should be used.");
			}
			if (!in_array($key, $this->validStyleSources)) {
				throw new Exception("Invalid style source: " . $key);
			}
			
			$internalStyles = $this->checkValidStyleset($value, "Style");
			$this->styleClasses[$key] =  htmlspecialchars($internalStyles, ENT_QUOTES);
		}
	}

	public function render() {
		if (!isset($this->headerText)) {
			throw new Exception("Header text is missing");
		}
		if (!$this->submitIsSet) {
			throw new Exception("Submit data is missing");
		}
		//if (!isset($this->mainText) || strlen($this->mainText) === 0) {
		//	throw new Exception("Main text is missing");
		//} 
		if (count($this->buttons) === 0) {
			throw new Exception("Buttons are missing");
		}
		$postData = $this->getPostData();
		$id = "";
		if (isset($this->formId)) {
			$id = ' id="' . $this->formId . '" ';
		}
		$formStyle = $this->getStyle(self::STYLE_FORM);
		$formHeaderStyle = $this->getStyle(self::STYLE_FORM_HEADER);
		$mainTextStyle = $this->getStyle(self::STYLE_MAIN_TEXT);
		$fieldAreaStyle = $this->getStyle(self::STYLE_FIELD_AREA);
		$buttonAreaStyle = $this->getStyle(self::STYLE_BUTTON_AREA);
		$footerAreaStyle = $this->getStyle(self::STYLE_FORM_FOOTER);

		echo '<form method="POST" action="' . $postData[self::URL] . '" name="' . $this->formName . '"' . $id . ' class="' . $formStyle . '">';
		echo '   <h2 class="' . $formHeaderStyle . '">' . $this->headerText . '</h2>';
		
		if (isset($this->mainText)) {
			echo '   <p class="' . $mainTextStyle . '">' . $this->mainText . '</p>';
		}
		$postfix = "";
		if (isset($this->inputFieldPostfix)) {
			$postfix = self::FIELD_POSTFIX_SEPARATOR . $this->inputFieldPostfix;
		}
		$this->renderFields($postfix,  $fieldAreaStyle);
		$this->renderButtons($postfix, $buttonAreaStyle);
		
		
		if (isset($this->footerText)) {
			
			echo '   <div class="' . $footerAreaStyle . '">' . $this->footerText . '</div>';
		}
		
		echo '</form>';
		
	}

	private function renderButtons($postfix, $buttonAreaStyle) {
		$i=0;
		echo '   <div class="' . $buttonAreaStyle . '">';
		
		foreach ($this->buttons as $button) {
			if ($button[self::TYPE] === self::BT_SUBMIT) {
				echo '      <input type= "submit" name="submit' . $postfix . '" value="' . $button[self::LABEL] . '" autofocus />';
			} else {
				echo '      <input type= "button" name="button' . $i++ . $postfix . '" value="' . $button[self::LABEL] . '" onClick="window.location=\'' . $button[self::URL] . '\';" />';
			}
			
		}
		echo '   </div>';
	} 

	private function renderFields($postfix,  $fieldAreaStyle) {
		if (isset($this->fields) && count($this->fields) > 0) {
			echo '   <div class="' . $fieldAreaStyle . '">';
			
			foreach ($this->fields as $data) { 
				$key = array_keys($data);
				$name = $key[0];
				$details =$data[$name];
				$label = $details[0];
				$type = $details[1];
				$value = "";
				$size = $details[3];
				$readonly  = $details[4];

				$hiddenStyle = "";
				if ($type === self::FT_HIDDEN) {
					$hiddenStyle = 'style="display: none;"';
				}
				echo '<div ' . $hiddenStyle . ' >';
				if ($type != self::FT_HIDDEN) {
					echo '<label for="' . $name . $postfix . '">'. $label . ':</label>';
				}
				$sizeToPrint = "";
				$title = $details[4];
				$titleToPrint = "";
				$readonlyToPrint = "";
				if (isset($title)) {
					$titleToPrint = ' title="'. $title . '"';
				}
				if (isset($readonly)) {
					if ($type === self::FT_CHECKBOX) {
						//Checkbox readonly has not too much effect disabled should be used
						$readonlyToPrint = ' disabled ';
					} else {
						$readonlyToPrint = ' readonly ';
					}
					
					
				}
				
				if ($type === self::FT_TEXTAREA) {
					$value = $details[2];
					if (isset($size)) {
						$sizeToPrint = ' cols="'. $size . '" ';
					}
					echo '<div><textarea name= "' . $name . $postfix . '"' . $sizeToPrint . $titleToPrint . $readonlyToPrint . ' >' . $value . '</textarea></div>';
				} else {
					if (isset($details[2])) {
						$value = ' value="' . $details[2] . '" ';
					}
					if (isset($size)) {
						$sizeToPrint = ' size="'. $size . '" ';
					}
					
					echo '<div><input type="' . $type . '" name= "' . $name . $postfix . '" ' . $value . $sizeToPrint . $titleToPrint . $readonlyToPrint  . '></div>';
				}
				echo '   </div>';
				

			}
			echo '   </div>';
		}
	}

	private function getPostData() {
		foreach ($this->buttons as $button) {
			if ($button[self::TYPE] === self::BT_SUBMIT) {
				return $button;
			}
		}
		//cannot happen
		return null;
	}

	private function checkValidString($string, $errorMessagePrefix) {
		return $this->checkValidStringImpl($string, $errorMessagePrefix, self::STRING_EXPRESSION);
	}

	private function checkValidSentences($string, $errorMessagePrefix) {
		return $this->checkValidStringImpl($string, $errorMessagePrefix, self::SENTENCE_EXPRESSION);
	}
	private function checkValidStyleset($string, $errorMessagePrefix) {
		return $this->checkValidStringImpl($string, $errorMessagePrefix, self::STYLESET_EXPRESSION);
	}

	private function checkValidId($string, $errorMessagePrefix) {
		if (!isset($string) || strlen(trim($string)) === 0 ) {
			throw new Exception($errorMessagePrefix . " is missing");
		}
		$string = trim($string);
		
		if (!ctype_alnum($string)) {
			throw new Exception($errorMessagePrefix . " must be alfanumerical!");
		}
		return $string;
	}
	private function checkValidStringImpl($string, $errorMessagePrefix, $expression) {
		if (!isset($string) || strlen(trim($string)) === 0 ) {
			throw new Exception($errorMessagePrefix . " is missing");
		}
		$string = trim($string);
		$stringToCheck = str_replace(' ', '', $string);
		
		if (!preg_match($expression, $stringToCheck)) { 
			
			throw new Exception($errorMessagePrefix . " must be alfanumerical!");
		} 
		return $string;
	}

	private function getStyle($source) {
		if (isset($this->styleClasses[$source])) {
			return $this->styleClasses[$source];
		} else {
			return "";
		}
		
		
	}

	
}