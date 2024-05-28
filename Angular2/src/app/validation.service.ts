import { FormControl } from '@angular/forms';

export class ValidationService {

    static getValidatorErrorMessage(validatorName: string, validatorValue?: any) {
        let config = {
            'required': 'Required',
            'invalidEmailAddress': 'Invalid email address',
            'currentPassword': 'Invalid current password',
            'primary': 'Set invalid primary emai',
            'bl_user_angular_settings':'Set invalid primary email',
            'addEmail':'This email already exists',
            'invalidPassword': 'Invalid password. Password must be at least 6 characters long, and contain a number.',
            'minlength': `Minimum length ${validatorValue?validatorValue.requiredLength:0}`,
            'invalidConfirmPassword': 'Passwords do not match, please retype'
        };

        return config[validatorName];
    }

    static emailValidator(control) {
        if (control.value && control.value.match(/[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/)) {
            return null;
        } else {
            return { 'invalidEmailAddress': true };
        }
    }

    static passwordValidator(control) {
        // {6,100}           - Assert password is between 6 and 100 characters
        // (?=.*[0-9])       - Assert a string has at least one number
        if (control.value && control.value.match(/^(?=.*[0-9])[a-zA-Z0-9!@#$%^&*]{6,100}$/)) {
            return null;
        } else {
            return { 'invalidPassword': true };
        }
    }

    //function for checking plain password validation
    static passwordsEqualValidator(c:FormControl) {

        if (c.value.password.length > 0 &&
            (c.value.plainPassword.length > 0 &&
            c.value.password != c.value.plainPassword)) {
            return {'invalidConfirmPassword' :true};
        }
        else{
            return null;
        }
    }
}