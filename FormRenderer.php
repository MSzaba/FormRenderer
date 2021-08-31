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
	private $fileFieldTypePresent = false;

	private const FILE_UPLOAD_MAX_SIZE = 128000;

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
	public const FT_SELECT = "select";
	//String expression to che the input
	private const STRING_EXPRESSION = '/^[a-zA-Z0-9öÖüÜóÓőŐúÚéÉáÁűŰíÍäÄ]+$/i';
	private const SENTENCE_EXPRESSION = '/^[a-zA-Z0-9öÖüÜóÓőŐúÚéÉáÁűŰíÍäÄ\.,:\"\'-_@\[\]&()–!?]+$/i';
	private const STYLESET_EXPRESSION = '/^[a-zA-Z0-9_\-\s]+$/i';

	private const ACCEPT_TYPES = [
		"audio/",
		"video/",
		"image/",
		"application/",
		"font/",
		"model/",
		"text/"
	];


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
	public const FP_SELECT_OPTIONS = "selectoptions";
	public const FP_ACCEPT= "accept";
	public const FP_REQUIRED = "required";

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
			self::FT_TEXTAREA,
			self::FT_SELECT
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
			self::FP_READONLY,
			self::FP_SELECT_OPTIONS,
			self::FP_ACCEPT,
			self::FP_REQUIRED
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

	protected function validateAcceptParameter($accept) {
		if ($accept == null || strlen($accept) == 0) {
			return false;
		}
		if (strpos($accept, ",") !== FALSE) {
			$elements = explode(",", $accept);
			foreach($elements as $element) {
				$result = $this->validAcceptParameterPart($element);
				//error_log("FormRenderer.validateAcceptParameter | result: " . $result);
				if (!$result) {
					return false;
				}
			}
			return true;
		} else {
			return  $this->validAcceptParameterPart($accept);
		}
		
	}
	
	private function validAcceptParameterPart($part) {
		//error_log("FormRenderer.validAcceptParameterPart | part: " . $part);
		foreach (self::ACCEPT_TYPES as $type) {
			//error_log("FormRenderer.validAcceptParameterPart | type: " . $type);
			if ($this->startsWith($part, $type)) {
				return true;
			}
		}
		if ($this->startsWith($part, ".") ) {
			$substr = substr($part, 1);
			//error_log("FormRenderer.validAcceptParameterPart | substring: " . $substr);
			return  !preg_match('/\s/',$substr); 
		}
		return false;
	}

	//utility method
	function startsWith( $haystack, $needle ) {
		$length = strlen( $needle );
		return substr( $haystack, 0, $length ) === $needle;
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
		$selectoptions = null;
		$accept = "";
		$required = false;


		if (isset($optionalParameters)) {
			if (isset($optionalParameters[self::FP_VALUE])) {
				$value = trim($optionalParameters[self::FP_VALUE]);
				$internalValue = htmlspecialchars($value, ENT_QUOTES);
				//error_log("FormRenderer.addField | value: " . $value . " internalValue: " . $internalValue);
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
			if (isset($optionalParameters[self::FP_SELECT_OPTIONS])) {
				$parameterValue = $optionalParameters[self::FP_SELECT_OPTIONS];
				if ($parameterValue == null || !is_array($parameterValue)) {
					throw new Exception("Select Options parameter is not an array!");
				}
				if (count($parameterValue) == 0) {
					throw new Exception("Select Options parameter array is empty!");
				}
				$processedParameters = array();
				foreach ($parameterValue as $key => $value) {
					//if (!ctype_alnum($key)) {
					//	error_log("FormRenderer::addField  Parameters: " . print_r($parameterValue, true));
					//	throw new Exception("Option name is not alfanumeric: " . $key);
					//}
					//if (!ctype_alnum($value)) {
					//	throw new Exception("Option value for key " . $key . " is not alfanumeric: " . $value);
					//}
					
					$processedParameters[htmlspecialchars($key, ENT_QUOTES)] = htmlspecialchars($value, ENT_QUOTES);
				}
				$selectoptions = $processedParameters;
			}
			if(isset($optionalParameters[self::FP_ACCEPT])) {
				$accept = $optionalParameters[self::FP_ACCEPT];
				if ($accept == null || strlen($accept) == 0) {
					throw new Exception("Accept parameter value is missing!");
				}
				if (!$this->validateAcceptParameter($accept)) {
					throw new Exception("Accept parameter value is invalid: " . $accept );
				}
			}
			if(isset($optionalParameters[self::FP_REQUIRED])) {
				$required = $optionalParameters[self::FP_REQUIRED];
				if ($required == null) {
					throw new Exception("Required parameter value is missing!");
				}
			}
		}

		
		if (array_key_exists($internalName, $this->fields)) {
			throw new Exception("Field has already added to the set!");
		}
		if (!in_array($type, $this->validFieldTypes)) {
			throw new Exception("Invalid field type!");
		}
		if ($type === self::FT_FILE) {
			$this->fileFieldTypePresent = true;
		}
		//array_push($this->fields, [$internalName => [$internalLabel, $type, $internalValue, $size, $title, $readonly, $selectoptions]]);
		$this->fields[$internalName] = [$internalLabel, $type, $internalValue, $size, $title, $readonly, $selectoptions, $accept, $required];
		//error_log("FormRenderer.addField | fields: " . print_r($this->fields, true));
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

	public function setValue($fieldName, $value) {
		if (!array_key_exists($fieldName, $this->fields)) {
			//error_log("FormRenderer.setValue || Unknown field name: " . $fieldName);
			throw new Exception("Unknown field name: " . $fieldName);
		}
		
		$fieldParameters = $this->fields[$fieldName];
		$valueToSet = null;
		//if (is_string($value)) {
		//	$fieldParameters[2] = htmlspecialchars($value);
		//} else {
		//	
		//}
		$fieldParameters[2] = $value;
		$this->fields[$fieldName] = $fieldParameters;
	}

	public function render() {
		//if (!isset($this->headerText)) {
		//	throw new Exception("Header text is missing");
		//}
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

		$encypeToRender = "";

		if ($this->fileFieldTypePresent) {
			//if file upload is used this enctype should be set
			$encypeToRender = 'enctype="multipart/form-data"';
		}

		echo '<form method="POST" action="' . $postData[self::URL] . '" name="' . $this->formName . '"' . $id . ' class="' . $formStyle . '"' . $encypeToRender . '" >';
		if (isset($this->headerText)) {
			echo '   <h2 class="' . $formHeaderStyle . '">' . $this->headerText . '</h2>';
		}
		
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
			if ($this->fileFieldTypePresent) {
				echo '<div visibility="hidden"><input type="hidden" name="MAX_FILE_SIZE' . $postfix .'" value="' . self::FILE_UPLOAD_MAX_SIZE . '" /></div>';
			}
			//error_log("FormRenderer.renderFields || fields: " . print_r($this->fields, true));
			foreach ($this->fields as $name => $details) { 
				$label = $details[0];
				$type = $details[1];
				$value = $details[2] ?? "";
				$size = $details[3];
				$title = $details[4];
				$readonly  = $details[5];
				$selectOptions = $details[6];
				$accept = $details[7];
				$required = $details[8];

				$hiddenStyle = "";
				if ($type === self::FT_HIDDEN) {
					$hiddenStyle = 'style="display: none; visibility: hidden;"';
				}
				echo '<div ' . $hiddenStyle . ' >';
				if ($type != self::FT_HIDDEN) {
					echo '<label for="' . $name . $postfix . '">'. $label . ':</label>';
				}
				$sizeToPrint = "";
				
				$titleToPrint = "";
				$readonlyToPrint = "";
				$requiredToPrint = "";

				if (isset($title) & strlen($title) > 0) {
					$titleToPrint = ' title="'. $title . '" ';
				}
				if ($readonly) {
					if ($type === self::FT_CHECKBOX) {
						//Checkbox readonly has not too much effect disabled should be used
						$readonlyToPrint = ' disabled ';
					} else {
						$readonlyToPrint = ' readonly ';
					}
					
					
				}
				if ($required) {
					$requiredToPrint = " required ";
				}
				

				//error_log("FormRenderer.renderFields || value: " . print_r($value, true));

				if ($type === self::FT_TEXTAREA) {
					if (isset($size) & strlen($size) > 0) {
						$sizeToPrint = ' cols="'. $size . '" ';
					}
					echo '<div><textarea name= "' . $name . $postfix . '"' . $sizeToPrint . $titleToPrint . $readonlyToPrint . $requiredToPrint . ' >' . $value . '</textarea></div>';
				} else if ($type === self::FT_SELECT) {

					echo '<div><select name= "' . $name . $postfix . '"' . $sizeToPrint . $titleToPrint . $readonlyToPrint . $requiredToPrint . ' >';
					foreach ($selectOptions as $optionValue => $optionText) {
						
						if (isset($value) && $value === $optionValue) {
							$selected = " selected "; 
							
						} else {
							$selected = "";
						}
						echo '<option value="' . $optionValue . '" ' . $selected . ' >' . $optionText . '</option>';
					}
					echo '</select></div>';
				} else if ($type === self::FT_DATETIME_LOCAL) {
					$valueToPrint = "";
					if (isset($value) && $value instanceof DateTime) {
						$valueToPrint = ' value="' . $value->format('Y-m-d\TH:i:s')  . '" ';
					}
					if (isset($size) & strlen($size) > 0) {
						$sizeToPrint = ' size="'. $size . '" ';
					}
					
					echo '<div><input type="' . $type . '" name= "' . $name . $postfix . '" ' . $valueToPrint . $sizeToPrint . $titleToPrint . $readonlyToPrint  . $requiredToPrint . '></div>';
				} else if ($type === self::FT_FILE) {
					
					$valueToPrint = "";
					$acceptToPrint = "";
					$sizeToPrint = "";
					if (isset($value) & strlen($value) > 0) {
						$valueToPrint = ' value="' . $value . '" ';
					}
					if (isset($size) & strlen($size) > 0) {
						$sizeToPrint = ' size="'. $size . '" ';
					}
					if (isset($accept) & strlen($accept) > 0) {
						$acceptToPrint = ' accept="' . $accept . '"';
						//error_log("FormRenderer.renderFields | accept: " . $accept);
					}
					
					echo '<div><input type="' . $type . '" name= "' . $name . $postfix . '" ' . $valueToPrint . $sizeToPrint . $titleToPrint . $readonlyToPrint  . $requiredToPrint . $acceptToPrint . '></div>';
				} else {
					$valueToPrint = "";
					$sizeToPrint = "";
					if (isset($value) & strlen($value) > 0) {
						$valueToPrint = ' value="' . $value . '" ';
					}
					if (isset($size)  & strlen($size) > 0) {
						$sizeToPrint = ' size="'. $size . '" ';
					}
					
					echo '<div><input type="' . $type . '" name= "' . $name . $postfix . '" ' . $valueToPrint . $sizeToPrint . $titleToPrint . $readonlyToPrint  . $requiredToPrint . '></div>';
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