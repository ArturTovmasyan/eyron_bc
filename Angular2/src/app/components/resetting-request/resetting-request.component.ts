import { Component, OnInit } from '@angular/core';
import { RouterModule, Routes, ActivatedRoute, Router, NavigationEnd } from '@angular/router';
import { ProjectService } from '../../project.service';
import {CacheService} from 'ng2-cache/ng2-cache';
import { Broadcaster } from '../../tools/broadcaster';
import { ValidationService } from '../../validation.service';
import { FormBuilder, Validators, FormGroup } from '@angular/forms';

@Component({
    selector: 'app-resetting-request',
    templateUrl: './resetting-request.component.html',
    styleUrls: ['./resetting-request.component.less']
})
export class ResettingRequestComponent implements OnInit {

    eventId: number = 0;
    type: string;
    secret: any = null;
    form: FormGroup;
    errorMessage:any;
    sub:any;
    ready:boolean = false;
    show:boolean = false;
    appUser:any;
    apikey:boolean = true;
    initForm:boolean = false;
    email:any = null;
    resendEmail:any;
    data:any = {};
    updatedEmail:any;
    isDestroy:boolean = false;
    projectName:any;

    constructor(private route: ActivatedRoute,
                private _projectService: ProjectService,
                private broadcaster: Broadcaster,
                private router:Router,
                private fb: FormBuilder,
                private _cacheService: CacheService
    ) {

        this.sub = router.events.subscribe((event) => {
            if(event instanceof NavigationEnd ) {
                if (!this.isDestroy && this.eventId != event.id) {
                    this.eventId = event.id;

                    this.type = this.route.snapshot.paramMap.has('type') ? this.route.snapshot.paramMap.get('type') : 'request';
                    this.secret = this.route.snapshot.paramMap.has('secret') ? this.route.snapshot.paramMap.get('secret') : null;

                    if (this.type == 'request') {

                        this.initSendEmailForm();
                        this.ready = true;
                    }

                    if (this.type == 'reset' && this.secret) {

                        if (this.errorMessage && this.errorMessage.email_token) {
                            this.router.navigate(['/error']);
                            this.errorMessage = null;
                        }

                        this.checkResetToken(this.secret);
                    }

                    if (this.type == 'check-email') {

                        if (!this.email) {
                            this.router.navigate(['/resetting/request']);
                        }
                    }

                    if (this.type == 'resend-message') {
                        this.checkRegisterToken(this.appUser.id);
                    }

                    if (this.type == 'update-email') {
                        this.checkRegisterToken(this.appUser.id);
                    }

                }
            }
            }
        );
    }

    ngOnInit() {

        this.broadcaster.broadcast('regConfirmMenu', false);
        this.projectName = this._projectService.getAngularPath();

        //get current user data
        if(localStorage.getItem('apiKey')){
            this.appUser = this._projectService.getMyUser();
            if (!this.appUser) {
                this.appUser = this._cacheService.get('user_');
                if(!this.appUser) {
                    this._projectService.getUser()
                        .subscribe(
                            user => {
                                this.appUser = user;
                                this._cacheService.set('user_', user, {maxAge: 3 * 24 * 60 * 60});
                                this.broadcaster.broadcast('getUser', user);
                            })
                }
            }
        }
    }

    ngOnDestroy(){
        this.sub.unsubscribe();
        this.isDestroy = true;
        this.broadcaster.broadcast('regConfirmMenu', true);
    }

    /**
     * This function is used to init reset email form
     */
    initSendEmailForm() {
        //create form validation
        this.form = this.fb.group({
            'email': ['', [Validators.required, ValidationService.emailValidator]]
        });
    }

    /**
     * This function is used to send resetting email
     */
    sendResettingEmail(data) {

        this.show = true;

        this._projectService.sendResettingEmail(data.email)
            .subscribe(
                () => {
                    this.email = data.email;
                    this.router.navigate(['/resetting/check-email']);
                    this.show = false;
                },
                error => {
                    this.errorMessage = JSON.parse(error._body);
                    this.show = false;
                }
            );

    }

    /**
     * This function is used to send confirm reg email
     */
    sendConfirmRegistrationEmail(data) {

        this.show = true;

        this._projectService.sendConfirmRegistrationEmail(data)
            .subscribe(
                (res) => {
                    this._cacheService.set('confirmRegEmail' + this.appUser.id, res, {maxAge: 3 * 24 * 60 * 60});

                    if(this.type !== 'resend-message') {
                        this.router.navigate(['/ideas']);
                    }

                    this.show = false;
                },
                error => {
                    this.errorMessage = JSON.parse(error._body);
                    if(this.errorMessage) {
                        this.router.navigate(['/ideas']);
                    }

                    this.show = false;
                }
            );
    }

    /**
     * This function is used to init change user password form
     */
    initChangePasswordForm() {

        //create form validation
        this.form = this.fb.group({
                'password': ['', [Validators.minLength(6), Validators.required, ValidationService.passwordValidator]],
                'plainPassword' : ['', [Validators.minLength(6), Validators.required]],
            },{validator: ValidationService.passwordsEqualValidator}
        );
    }

    /**
     * This function is used to send new password data
     */
    changePassword(data) {

        this.show = true;
        data['token'] = this.secret;
        data['apikey'] = this.apikey;

        this._projectService.changePassword(data)
            .subscribe(
                (res) => {
                    if(res.apiKey) {

                        localStorage.setItem('apiKey', res.apiKey);
                        this.broadcaster.broadcast('login', res.userInfo);
                        this.router.navigate(['/ideas']);
                        this.show = false;
                    }
                },
                error => {
                    this.errorMessage = JSON.parse(error._body);
                    this.show = false;
                }
            );
    }

    /**
     * This function is used to check reset password token
     */
    checkResetToken(token) {

        this._projectService.checkResetToken(token)
            .subscribe(
                (res) => {
                    if(res.confirm) {
                        this.initChangePasswordForm();
                        this.ready = true;
                    }
                },
                error => {
                    this.errorMessage = JSON.parse(error._body);

                    if(this.errorMessage.email_token) {
                        this.broadcaster.broadcast('error', this.errorMessage.email_token);
                        this.router.navigate(['/error']);
                    }
                }
            );
    }

    /**
     * This function is used to check registration token
     */
    checkRegisterToken(data) {

        this.data['id'] = data;

        this._projectService.checkRegisterToken(this.data)
            .subscribe(
                (res) => {
                    if(!res.confirm) {
                        this.router.navigate(['/ideas']);
                        return;
                    }

                    if(this.type == 'resend-message' && res.confirm) {
                        this.sendConfirmRegistrationEmail(null);
                        this.resendEmail = this.appUser.username;
                        this.ready = true;
                    }

                    if(this.type == 'update-email' && res.confirm) {
                        this.updatedEmail = this._cacheService.get('confirmRegEmail' + this.appUser.id);
                        this.initSendEmailForm();
                        this.ready = true;
                    }
                },
                error => {
                    this.errorMessage = JSON.parse(error._body);
                    if(this.errorMessage) {
                        this.router.navigate(['/error']);
                    }
                }
            );
    }
}
