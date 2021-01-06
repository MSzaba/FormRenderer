# FormRenderer
PHP reusable from renderer widget.

It is part of a hobby porject. Since it a reusable component, and independent from the other parts of the hobby project it makes sense to publish it.

## The main features are:
------------------------
- Set header text of the form
- Set the main message
- Add fileds to the form
- Add buttons to the form
- Set a security related form field name postfix


### Example:
----------
```
$usernameFiledOptions = [
	FormRenderer::FP_SIZE => 50,
	FormRenderer::FP_TITLE => "Username must be 8 characters long"
];
$passwordFiledOptions = [
	FormRenderer::FP_SIZE => 50,
	FormRenderer::FP_TITLE => "Password should contain special characters"
];
$XSRFFiledOptions = [
	FormRenderer::FP_VALUE => $predefinedXSRFToken
];

$fr = new FormRenderer("CustomLoginForm");
$fr->setHeaderText("Form Header");
$fr->setMainText("Enter your username and password to login);
$fr->setStyles([FormRenderer::STYLE_FORM => "pixel500 center topMargin10 outerDiv bottomMargin10"]);
$fr->setStyles([FormRenderer::STYLE_FORM_HEADER => "input_header"]);
$fr->setStyles([FormRenderer::STYLE_MAIN_TEXT => "mainText"]);
$fr->setStyles([FormRenderer::STYLE_FIELD_AREA => "fieldArea"]);
$fr->setStyles([FormRenderer::STYLE_BUTTON_AREA => "buttonArea"]);
$fr->setStyles([FormRenderer::STYLE_FORM_FOOTER => "footerArea"]);
$fr->setSubmitButton("Send", "/processForm.php");
$fr->addButton("Cancel", "/index.php");
$fr->setInputFileldPostfix($predefinedPostfix);
$fr->addField("any", SecurityHelper::XSRFToken, FormRenderer::FT_HIDDEN, $XSRFFiledOptions);
$fr->addField("Username", "username", FormRenderer::FT_TEXT, $usernameFiledOptions);
$fr->addField("Password", "password", FormRenderer::FT_PASSWORD, $passwordFiledOptions);
$fr->setFooterText("Please read the <a href="/termsOfUsage.php">Terms of Usage</a>!", true);
$fr->render();
```
On the first line we create the form Renderer object.

### Parameters:
------------
- $formName - will be used as form name
- $formId - (optional) will be used as form Id

Then the setHeaderText method was called. This will print the form header. It is mandatory to set a string. Special characters will be escaped
The method setMainText provide an area to print some text above the fields.
The method setStyles can be used to set the styles of the differnet elements of the form.

## The following constants can be used to specify the area:
--------------------------------------------------------
- FormRenderer::STYLE_FORM
- FormRenderer::STYLE_FORM_HEADER
- FormRenderer::STYLE_MAIN_TEXT
- FormRenderer::STYLE_FIELD_AREA
- FormRenderer::STYLE_BUTTON_AREA
- FormRenderer::STYLE_FORM_FOOTER

The method setSubmitButton sets the label of the button and target of the form.

The method addButton can add further button to the form, an inline script will be used for navigation.
Please note: inline scripts should be allowed to make it work! You can use "script-src  'self' 'unsafe-inline';" in <meta http-equiv="Content-Security-Policy" tag of the head

The setInputFileldPostfix method can add a postfix for every input field. This can be detected during form processing and invalid access can be detected this way.

We use the addField three times here. First we add a hidden field to contain an XSRF token to protect the page, then a field for username nad password.

## The parameters of the addField methos are the following:
--------------------------------------------------------
- $label - Label of the field
- $name - Name of the field
- $type -Type tof the field
- $optionalParameters -Some optional parameters can be set for the fields, this array contains them

## The valid field types are the following:
-----------------------------------------
- FormRenderer::FT_CHECKBOX
- FormRenderer::FT_COLOR
- FormRenderer::FT_DATE
- FormRenderer::FT_DATETIME_LOCAL
- FormRenderer::FT_EMAIL
- FormRenderer::FT_FILE
- FormRenderer::FT_HIDDEN
- FormRenderer::FT_IMAGE
- FormRenderer::FT_MONTH
- FormRenderer::FT_NUMBER
- FormRenderer::FT_PASSWORD
- FormRenderer::FT_RADIO
- FormRenderer::FT_SEARCH
- FormRenderer::FT_TEXT
- FormRenderer::FT_TIME
- FormRenderer::FT_TEXTAREA
- FormRenderer::FT_SELECT

### The supported optional parametes are the following:
- FormRenderer::FP_TITLE
- FormRenderer::FP_SIZE
- FormRenderer::FP_VALUE
- FormRenderer::FP_READONLY
- FormRenderer::FP_SELECT_OPTIONS

The fill the related HTML parameters of the tag. In the example all 3 are used

After a button area there is a footer text area. Here a link was placed to terms of usage.

### The method setFooterText parameters are the following:
------------------------------
- $footerText - the text to be printed
- $containsHTML - (Optional) if true, the special characters won't be escaped

Then the render method do the actual rendering process.


## Using dropdown list in forms:
------------------------------

The field type FT_SELECT allows usage of dropdown list. In this case the FP_SELECT_OPTIONS parmaeter should be passed to the Form Renderer.
Here is an example:
```
$options = [
	FormRenderer::FP_SELECT_OPTIONS => [
		"volvo" => "Volvo",
		"saab" => "Saab",
		"opel" => "Opel",
		"audi" => "Audi",
	]
];
$fr = new FormRenderer("testForm", "testFormId");
$fr->addField("Label 1", "Name1", FormRenderer::FT_SELECT, $options);
```
