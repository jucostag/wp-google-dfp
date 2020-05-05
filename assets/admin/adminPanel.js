document.addEventListener('DOMContentLoaded', function (event){
    var adminPanel = new GDFPAdminPanel();
    adminPanel.init();
});

GDFPAdminPanel = function(){
    this.editForm = document.querySelectorAll('.gdfp_edit');
    this.createURLForm = document.querySelector('.gdfp_create_urls_form');
    this.deleteURLForms = document.querySelectorAll('.gdfp_delete_url_form');
    this.createFormatsGroupForm = document.querySelector('.gdfp_create_format_groups_form');
    this.createURLFieldTrigger = document.querySelector('.gdfp_add_url_field');
    this.createFormURLFields = document.querySelector('.gdfp_urls_inputs');
    this.firstFormatCheckbox = document.querySelectorAll('.gdfp_first_checkbox');
    this.createFormatCheckboxTrigger = document.querySelectorAll('.gdfp_add_format_checkbox');
    this.removeFormatCheckboxTrigger = document.querySelectorAll('.gdfp_remove_format_checkbox');
};

GDFPAdminPanel.prototype.init = function(){
    this.confirmURLExclusion();
    this.showActionStatus();
    this.showEditForm();
    this.addURLToCreateForm();
    this.enableAddRemoveCheckboxes();
    this.addFormatToGroupsForm();
    this.removeCheckboxesFromGroupsForm();
    this.showContentFormatsForm();
};

GDFPAdminPanel.prototype.confirmURLExclusion = function (){
    for (var i = 0; i < this.deleteURLForms.length; i++) {
        this.deleteURLForms[i].onsubmit = function (event){
            event.preventDefault();
            if (confirm('Tem certeza de que deseja excluir a URL?')) {
                this.submit();
            }
        };
    }
};

GDFPAdminPanel.prototype.showActionStatus = function(){
    var actionStatus = document.querySelector('.gdfp_action_status');

    if(!actionStatus) {
        return;
    }

    if (actionStatus.classList.contains('active')) {
        actionStatus.style.display = 'block';

        setTimeout(function(){
            actionStatus.style.display = 'none';
        }, 3000);

        actionStatus.classList.remove('active');
    }
};

GDFPAdminPanel.prototype.showEditForm = function(){
    if(!this.editForm) {
        return;
    }

    for (var i = 0; i < this.editForm.length; i++) {
        this.editForm[i].addEventListener('click', function (event){
            event.preventDefault();
            var formId = event.target.dataset.form;
            
            if (event.target.classList.contains('dashicons')) {
                formId = event.target.parentNode.dataset.form;
            }

            var form = document.getElementById(formId);
            form.style.display = 'block';
        });
    }
};

GDFPAdminPanel.prototype.addURLToCreateForm = function (){
    if(!this.createURLForm) {
        return;
    }

    var self = this;
    this.createURLFieldTrigger.addEventListener('click', function (event){
        event.preventDefault();
        var labelCount = document.querySelectorAll('.gdfp_input_url_label').length + 1,
        fieldContainer = document.createElement("DIV"),
        labelInputURL = document.createElement('LABEL'),
        inputURL = document.createElement('INPUT');

        labelInputURL.classList.add('gdfp_input_url_label');
        var labelText = document.createTextNode("URL " + labelCount);
        labelInputURL.appendChild(labelText);

        inputURL.type = 'text';
        inputURL.name = 'slot_url[]';
        inputURL.classList.add('create_input');
        inputURL.classList.add('gdfp_text_input');

        fieldContainer.appendChild(labelInputURL);
        fieldContainer.appendChild(inputURL);
        self.createFormURLFields.appendChild(fieldContainer);
    });
};

GDFPAdminPanel.prototype.enableAddRemoveCheckboxes = function (){
    if (!this.createFormatsGroupForm) {
        return;
    }

    for (var i = 0; i < this.firstFormatCheckbox.length; i++) {
        this.firstFormatCheckbox[i].addEventListener('change', function (event){
            var checked = event.target.checked,
            relatedButtons = document.querySelectorAll('.gdfp_' + event.target.dataset.format + '_btn');

            for (var i = 0; i < relatedButtons.length; i++) {
                if (checked) {
                    relatedButtons[i].disabled = false;
                } else {
                    relatedButtons[i].disabled = true;
                }
            }
            
        });
    }
};

GDFPAdminPanel.prototype.addFormatToGroupsForm = function(){
    if (!this.createFormatsGroupForm) {
        return;
    }

    var self = this;

    for (var i = 0; i < this.createFormatCheckboxTrigger.length; i++) {
        this.createFormatCheckboxTrigger[i].addEventListener('click', function (event){
            event.preventDefault();

	    var target = self.getEventTarget(event.target),
            format = target.dataset.format,
            checkboxesContainer = self.getCheckboxContainer(target, format),
            formatCheckboxesCount = checkboxesContainer.querySelectorAll('input').length,
            labelContainer = document.createElement('LABEL'),
            newCheckbox = document.createElement('INPUT');

            newCheckbox.type = 'checkbox';
            newCheckbox.name = 'group_formats[]';
            newCheckbox.checked = true;
            formatCheckboxesCount = (formatCheckboxesCount > 0) ? formatCheckboxesCount : '';
            newCheckbox.value = format + formatCheckboxesCount;

            labelContainer.appendChild(newCheckbox);
            var labelText = document.createTextNode(format + formatCheckboxesCount);
            labelContainer.appendChild(labelText);

            checkboxesContainer.appendChild(labelContainer); 
        });
    }
};

GDFPAdminPanel.prototype.removeCheckboxesFromGroupsForm = function (){
    if (!this.createFormatsGroupForm) {
        return;
    }

    var self = this;

    for (var i = 0; i < this.removeFormatCheckboxTrigger.length; i++) {
        this.removeFormatCheckboxTrigger[i].addEventListener('click', function (event){
            event.preventDefault();

            var target = self.getEventTarget(event.target),
            format = target.dataset.format,
            checkboxesContainer = self.getCheckboxContainer(target, format),
            labels = checkboxesContainer.getElementsByTagName('label');

            if (labels.length > 1) {
                checkboxesContainer.removeChild(checkboxesContainer.lastChild);
            }
        });
    }
};

GDFPAdminPanel.prototype.getEventTarget = function (target){
    if (target.classList.contains('dashicons')) {
        return target.parentNode;
    }

    return target;
};

GDFPAdminPanel.prototype.getCheckboxContainer = function (target, format){
    var checkboxGroup = '.gdfp_' + format + '_checkbox_group';

    if (target.classList.contains('gdfp_edit_group_btn')) {
        var group = target.dataset.group;
        return document.querySelector('#gdfp_edit_group_' + group + ' ' + checkboxGroup);
    }

    return document.querySelector(checkboxGroup);
};

GDFPAdminPanel.prototype.showContentFormatsForm = function (){
    var triggers = document.querySelectorAll('.gdfp_content_form_btn');

    for (var i = 0; i < triggers.length; i++) {
        triggers[i].addEventListener('click', function(event){
            var formId = event.target.dataset.open,
            form = document.querySelector('#gdfp_content_formats_form_' + formId),
            icon = event.target.querySelector('.dashicons');

            if(form.style.display == 'none') {
                jQuery(form).slideDown();                
                icon.classList.remove('dashicons-arrow-down-alt2');
                icon.classList.add('dashicons-arrow-up-alt2');
                return;
            } 

            jQuery(form).slideUp();
            icon.classList.remove('dashicons-arrow-up-alt2');
            icon.classList.add('dashicons-arrow-down-alt2');            
        });
    }
    
};