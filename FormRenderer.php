<?php
if (!defined("__ALLOW_INCLUDE__")) {
		exit("Obsolete content confirmation Page");
	}

class FormRenderer {
	
	private $formName;
	private $formId;
	private $mainText;
	private $headerText;
	private $buttons;
	private $fields;
	private $submitIsSet;
	private $inputFieldPostfix;
	private $validFieldTypes;
	private const BT_SUBMIT = "S";
	private const BT_OPTION = "O";
	private const URL = "U";
	private const LABEL = "L";
	private const TYPE = "T";
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

	public function __construct($formName, $formId = null) {
		if (!isset($formName)) {
			throw new Exception("Form name is misisng!");
		}
		$formName = trim($formName);
		$formName = str_replace(' ', '', $formName);
		if (!ctype_alnum($formName)) {
			throw new Exception("Form name is invalid!");
		}
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
	}

	public function setMainText($mainText) {
		if (isset($this->mainText)) {
			throw new Exception("Main text is already set!");
		}
		if (!isset($mainText) || strlen(trim($mainText)) === 0) {
			throw new Exception("Main text is missing");
		} 
		$mainText = trim($mainText);
		$this->mainText = htmlspecialchars($mainText, ENT_QUOTES);

	}
	public function setHeaderText($headerText) {
		if (isset($this->headerText)) {
			throw new Exception("Header text is already set!");
		}
		if (!isset($headerText) || strlen(trim($headerText)) === 0) {
			throw new Exception("Header text is missing");
		} 
		$headerText = trim($headerText);
		$this->headerText = htmlspecialchars($headerText, ENT_QUOTES);

	}

	public function addField($label, $name, $type, $value = null) {
		if (!isset($label)) {
			throw new Exception("Label is misisng!");
		}
		$label = trim($label);
		$labelToCheck = str_replace(' ', '', $label);
		if (!ctype_alnum($labelToCheck)) {
			throw new Exception("Label must be alfanumerical!");
		}
		if (!isset($name)) {
			throw new Exception("Name is misisng!");
		}
		$name = trim($name);
		$nameToCheck = str_replace(' ', '', $name);
		if (!ctype_alnum($nameToCheck)) {
			throw new Exception("Name must be alfanumerical!");
		}
		if (!isset($type)) {
			throw new Exception("Type is misisng!");
		}
		$internalName = htmlspecialchars($name, ENT_QUOTES);
		$internalLabel = htmlspecialchars($label, ENT_QUOTES);
		$internalValue = "";
		if (isset($value)) {
			$value = trim($value);
			$internalValue = htmlspecialchars($value, ENT_QUOTES);
		}
		if (array_key_exists($internalName, $this->fields)) {
			throw new Exception("Field has already added to the set!");
		}
		if (!in_array($type, $this->validFieldTypes)) {
			throw new Exception("Invalid field type!");
		}
		array_push($this->fields, [$internalName => [$internalLabel, $type, $internalValue]]);
	}

	//This string is added to the non-button type input fields as an extra security feature.
	//With this it can be check, whether the post request belongs to the actual user.
	public function setInputFileldPostfix($postfix) {
		if (isset($this->postfix)) {
			throw new Exception("Input Field Postfix is already set!");
		}
		if (!ctype_alnum($postfix)) {
			throw new Exception("Postfix must be alfanumerical!");
		}
		$this->postfix = $postfix;
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
		if (!isset($label) || strlen($label) === 0) {
			throw new Exception("Label is missing");
		}
		$label = trim($label);
		$labelToCheck = str_replace(' ', '', $label);
		if (!ctype_alnum($labelToCheck)) {
			throw new Exception("Label must be alfanumerical!: " . $labelToCheck);
		}
		if (!isset($url) || strlen($url) === 0) {
			throw new Exception("URL is missing");
		}
		$button = [self::LABEL => htmlspecialchars($label, ENT_QUOTES),
					self::URL =>  htmlspecialchars($url, ENT_QUOTES),
					self::TYPE => $type];
		array_push($this->buttons, $button);
	}

	public function render() {
		if (!isset($this->headerText)) {
			throw new Exception("Header text is missing");
		}
		if (!$this->submitIsSet) {
			throw new Exception("Submit data is missing");
		}
		if (!isset($this->mainText) || strlen($this->mainText) === 0) {
			throw new Exception("Main text is missing");
		} 
		if (count($this->buttons) === 0) {
			throw new Exception("Buttons are missing");
		}
		$postData = $this->getPostData();
		$id = "";
		if (isset($this->formId)) {
			$id = ' id="' . $this->formId . '" ';
		}
		
		echo '<form method="POST" action="' . $postData[self::URL] . '" name="' . $this->formName . '"' . $id . ' class="pixel500 center">';
		echo '   <h2 class="input_header">' . $this->headerText . '</h2>';
		
		echo '   <p>' . $this->mainText . '</p>';
		echo '   <div>';
		foreach ($this->fields as $data) { //  $name => 
			//var_dump($data);
			//var_dump($data[$name]);
			//$details = $this->fields[$name];
			
			//var_dump($details);
			$key = array_keys($data);
			$name = $key[0];
			$details =$data[$name];
			//var_dump($key);
			//var_dump($data[$key[0]]);
			$label = $details[0];
			$type = $details[1];
			$value = "";
			echo '<div>';
			if ($type != self::FT_HIDDEN) {
				echo '<div>'. $label . '</div>';
			}
			
			if ($type === self::FT_TEXTAREA) {
				$value = $details[2];
				echo '<div><textarea name= "' . $name . '" >' . $value . '</textarea></div>';
			} else {
				if (isset($details[2])) {
					$value = ' value="' . $details[2] . '" ';
				}
				echo '<div><input type="' . $type . '" name= "' . $name . '" ' . $value . '></div>';
			}
			echo '   </div>';
			

		}
		echo '   </div>';
		$i=0;
		echo '   <div>';
		foreach ($this->buttons as $button) {
			if ($button[self::TYPE] === self::BT_SUBMIT) {
				echo '      <input type= "submit" name="submit" value="' . $button[self::LABEL] . '" autofocus />';
			} else {
				echo '      <input type= "button" name="button' . $i . '" value="' . $button[self::LABEL] . '" onClick="window.location=\'' . $button[self::URL] . '\';" />';
			}
			
		}
		echo '   </div>';
		echo '</form>';
		
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

	private function checkValidString($string, $errorMessgaePrefix) {
		if (!isset($string)) {
			throw new Exception($errorMessgaePrefix . " is missing");
		}
	}
}