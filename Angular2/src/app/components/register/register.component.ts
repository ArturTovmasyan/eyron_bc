import { Component, OnInit } from '@angular/core';
import { FormBuilder, Validators, FormGroup } from '@angular/forms';
import { ValidationService } from '../../validation.service';
import { ProjectService} from '../../project.service';
import { Broadcaster } from '../../tools/broadcaster';
import {CacheService} from 'ng2-cache/ng2-cache';
import { Router } from '@angular/router';
import { Uploader }      from 'angular2-http-file-upload';
import { MyUploadItem }  from '../my-dropzone/my-upload';

@Component({
    selector: 'app-register',
    templateUrl: './register.component.html',
    styleUrls: ['./register.component.less']
})
export class RegisterComponent implements OnInit {

    form: FormGroup;
    source:string;
    arrayDay:number[] = [];
    arrayYear:number[] = [];
    errorMessage:any = null;
    day:any = 0;
    month:any = 0;
    year:any = 0;
    birthDay:any;
    path:string = '/api/v1.0/user/upload-file';
    file:any;
    imageError:any;
    show: boolean = false;

    //create date value
    public arrayMonth:Array<string> = [
        'form.birth_date_month',
        'form.month_january',
        'form.month_february',
        'form.month_march',
        'form.month_april',
        'form.month_may',
        'form.month_june',
        'form.month_july',
        'form.month_august',
        'form.month_september',
        'form.month_october',
        'form.month_november',
        'form.month_december'
    ];

    constructor(
        private _projectService: ProjectService,
        private fb: FormBuilder,
        private router: Router,
        private broadcaster: Broadcaster,
        public uploaderService: Uploader,
        private _cacheService: CacheService)
    {}

    ngOnInit() {

        //create form validation
        this.form = this.fb.group({
                'file': ['', null],
                'apikey': [true, null],
                'firstName': ['', [Validators.required]],
                'lastName': ['', [Validators.required]],
                'email': ['', [Validators.required, ValidationService.emailValidator]],
                'password': ['', [Validators.required, Validators.minLength(6), ValidationService.passwordValidator]],
                'plainPassword' : ['', [Validators.required, Validators.minLength(6), ValidationService.passwordValidator]],
                'month' : [this.month, null],
                'year' : [this.year, null],
                'day' : [this.day, null]
            }, {validator: ValidationService.passwordsEqualValidator}
        );

        this.createDays(31);
        this.createYears(1917, 2017);
    }

    createDays(number) {
        for (let i = 1; i <= number; i++) {
            this.arrayDay.push(i);
        }
    }

    createYears(number1, number2) {
        for (let i = number2; i>= number1; i--) {
            this.arrayYear.push(i);
        }
    }

    /**
     *
     * @param registerData
     */
    createUser(registerData:any) {

        this.show = true;

        if(registerData.day!=0 && registerData.month!=0 && registerData.year!=0) {
            //generate birthday value
            this.birthDay = registerData.day+'/'+registerData.month+'/'+registerData.year;
            registerData['birthday'] = this.birthDay;
        }else{
            this.birthDay = '';
        }
        
        //remove day,month,year
        delete registerData.day;
        delete registerData.year;
        delete registerData.month;

        this._projectService.putUser(registerData)
            .subscribe(
                res => {
                    if(res.apiKey) {
                        localStorage.setItem('apiKey', res.apiKey);
                        this.saveImage(res.userInfo);
                        this._cacheService.set('confirmRegEmail' + res.userInfo.id, res.userInfo.username, {maxAge: 3 * 24 * 60 * 60});
                        this.show = false;
                        this.broadcaster.broadcast('regConfirmMenu', true);
                        this.router.navigate(['/ideas']);
                    }
                },
                error => {
                    this.errorMessage = JSON.parse(error._body);
                }
            );
    }

    /**
     *
     * @param event
     */
    showUploadedImage(event){
        let input = event.target;

        if (input.files && input.files[0]) {
            this.file = input.files[0];
            let reader = new FileReader();

            reader.onload = (e:any) => {
                if(e && e.target){
                    this.source = e.target.result;
                }
            };

            reader.readAsDataURL(input.files[0]);
        }
    }

    saveImage(userInfo){

        if(this.file){
            let myUploadItem = new MyUploadItem(this.file, this._projectService.getPath() + this.path);
            // myUploadItem.formData = { FormDataKey: 'Form Data Value' };  // (optional) form data can be sent with file

            this.uploaderService.onSuccessUpload = (item, response, status, headers) => {
                this.imageError = null;
                userInfo.cached_image = response;
                this.broadcaster.broadcast('login', userInfo);
                this.router.navigate(['/ideas']);
            };
            this.uploaderService.onErrorUpload = (item, response, status, headers) => {
                this.imageError = response;
                this.errorMessage = response;
                this.broadcaster.broadcast('login', userInfo);
                this.router.navigate(['/edit/profile']);
            };
            this.uploaderService.onCompleteUpload = (item, response, status, headers) => {
                // this.existing[this.existing.length -1].progress = false;
            };
            this.uploaderService.upload(myUploadItem);
        } else {
            this.broadcaster.broadcast('login', userInfo);
            this.router.navigate(['/ideas']);
        }

    }

    openSignInPopover(){
        this.broadcaster.broadcast('openLogin', 'Open Login Please');
    }
}
