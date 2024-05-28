import { Component, OnInit,ViewContainerRef, OnDestroy } from '@angular/core';
import { RouterModule, Routes, ActivatedRoute, Router, NavigationEnd } from '@angular/router';
import { ProjectService} from '../project.service';
import { CacheService, CacheStoragesEnum} from 'ng2-cache/ng2-cache';
import { TranslateService} from 'ng2-translate';
import { Broadcaster } from '../tools/broadcaster';
import { ValidationService } from '../validation.service';
import { FormBuilder, Validators, FormGroup } from '@angular/forms';
import { RemoveProfileComponent} from "../modals/remove-profile/remove-profile.component";
import { MdDialog, MdDialogRef, MdDialogConfig} from '@angular/material';

@Component({
  selector: 'app-settings',
  templateUrl: './settings.component.html',
  styleUrls: ['./settings.component.less']
})

export class SettingsComponent implements OnInit, OnDestroy {

    eventId: number = 0;
    type: string;
    appUser:any;
    isDestroy: boolean = false;
    form: FormGroup;
    arrayDay:number[] = [];
    arrayYear:number[] = [];
    currentLang: string;
    message: string = '';
    token:boolean = true;
    ready:boolean = false;
    userEmails:any;
    socialEmail:any;
    errorMessage:any;
    lng:any = 'en';
    item :any= [];
    saveMessage:any;
    removeMessage:any;
    birthDate:any;
    sub:any;
    addMail:any = null;
    secret:any = null;
    email:any;
    day:any = 0;
    month:any = 0;
    year:any = 0;
    notifySettings:any;
    show:boolean = false;
    loading:boolean = false;
    isMobile:Boolean= (window.innerWidth < 768);
    removeProfileModal: MdDialogRef<RemoveProfileComponent>;

    languages: any[] = [
        {
            value:'en',
            name: 'English'
        },
        {
            value:'ru',
            name: 'Russian'
        }
    ];

    plainPassword: any = {
        first: '',
        second: ''
    };

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
    public months_3:Array<string> = [
        'form.birth_date_month',
        'form.month_january_3',
        'form.month_february_3',
        'form.month_march_3',
        'form.month_april_3',
        'form.month_may_3',
        'form.month_june_3',
        'form.month_july_3',
        'form.month_august_3',
        'form.month_september_3',
        'form.month_october_3',
        'form.month_november_3',
        'form.month_december_3'

    ];

    constructor(
        private _translate: TranslateService,
        private route: ActivatedRoute,
        private _projectService: ProjectService,
        private _cacheService: CacheService,
        private broadcaster: Broadcaster,
        private router:Router,
        private fb: FormBuilder,
        private viewContainerRef: ViewContainerRef,
        public dialog: MdDialog
    ) {
        this.sub = router.events.subscribe((event) => {
            if(event instanceof NavigationEnd ) {
                if (!this.isDestroy && this.eventId != event.id) {

                    this.eventId = event.id;
                    window.scrollTo(0, 0);
                    this.type = this.route.snapshot.paramMap.has('type') ? this.route.snapshot.paramMap.get('type') : 'profile';

                    this.form = null;
                    this.ready = false;

                    if (this.type == 'profile') {
                        this.saveMessage = false;
                        this.removeMessage = false;
                        this.initProfileForm();
                        this.ready = true;
                        this.loading = false;
                    }

                    if (this.type == 'notification') {
                        this.saveMessage = false;
                        this.removeMessage = false;
                        this.getNotifySettings();
                    }

                    if (this.type == 'add-email') {

                        if (this.errorMessage) {
                            this.router.navigate(['/error']);
                            this.errorMessage = null;
                        }

                        this.secret = this.route.snapshot.paramMap.has('secret') ? this.route.snapshot.paramMap.get('secret') : null;
                        this.addMail = this.route.snapshot.paramMap.has('addMail') ? this.route.snapshot.paramMap.get('addMail') : null;
                        this.activationUserAddEmail(this.secret, this.addMail);
                    }

                    if(this.removeProfileModal) {
                        this.removeProfileModal.close();
                    }

                    this.getUserInfoByType();
                }
            }
        });

        this.createDays(31);
        this.createYears(1917, 2017);

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

    initNotifyForm() {

        // create form validation
        this.form = this.fb.group({
                'isCommentOnGoalNotify': [(this.notifySettings?this.notifySettings.is_comment_on_goal_notify:false), null],
                'isCommentOnIdeaNotify': [(this.notifySettings?this.notifySettings.is_comment_on_idea_notify:false), null],
                'isSuccessStoryOnGoalNotify': [(this.notifySettings?this.notifySettings.is_success_story_on_goal_notify:false), null],
                'isSuccessStoryOnIdeaNotify': [(this.notifySettings?this.notifySettings.is_success_story_on_idea_notify:false), null],
                'isSuccessStoryLikeNotify': [(this.notifySettings?this.notifySettings.is_success_story_like_notify:false), null],
                'isGoalPublishNotify' : [(this.notifySettings?this.notifySettings.is_goal_publish_notify:false), null],
                'isCommentReplyNotify' : [(this.notifySettings?this.notifySettings.is_comment_reply_notify:false), null],
                'isDeadlineExpNotify' : [(this.notifySettings?this.notifySettings.is_deadline_exp_notify:false), null],
                'isNewGoalFriendNotify' : [(this.notifySettings?this.notifySettings.is_new_goal_friend_notify:false), null],
                'isNewIdeaNotify' : [(this.notifySettings?this.notifySettings.is_new_idea_notify:false), null]
            }
        );
    }

    initProfileForm() {

        if(this.appUser.user_emails) {
            this.userEmails = Object.keys(this.appUser.user_emails);
            this.checkEmailToken(this.appUser);

        } else{
            this.userEmails = null;
        }

        if(this.appUser.social_email) {
            this.socialEmail = this.appUser.social_email;
        } else{
            this.socialEmail = null;
        }

        this.email = this.appUser.username;

        if(this.appUser.birth_date) {
            this.birthDate = new Date(this.appUser.birth_date);
            this.year = this.birthDate.getFullYear();
            this.month = this.birthDate.getMonth() + 1;
            this.day = this.birthDate.getDate();
        }

        if(this.appUser.language) {
            this.lng = this.appUser.language;
        }

        this.email = this.appUser.username;

        //create form validation
        this.form = this.fb.group({
                'firstName': [this.appUser.first_name, [Validators.required]],
                'lastName': [this.appUser.last_name, [Validators.required]],
                'email': [this.email, [ValidationService.emailValidator, Validators.required]],
                'currentPassword': ['', [Validators.minLength(6)]],
                'password': ['', [Validators.minLength(6)]],
                'plainPassword' : ['', [Validators.minLength(6)]],
                'primary' : [this.email, null],
                'language' : [this.lng, [Validators.required]],
                'addEmail' : ['', null],
                'month' : [this.month, null],
                'year' : [this.year, null],
                'day' : [this.day, null]
            }, {validator: ValidationService.passwordsEqualValidator}
        );
    }

    ngOnDestroy(){
        this.sub.unsubscribe();
        this.isDestroy = true;
    }
    
    ngOnInit() {
        if(this.isMobile){
            this.arrayMonth = this.months_3;
        }
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

    getUserInfoByType(){
      this.currentLang = this._translate.currentLang;
   }

    /**
     * This function is used to refresh user data and form
     *
     * @param data
     */
   refreshUserAndForm(data:any)
   {
       this._projectService.setMyUser(null);
       this.appUser = data;
       this.broadcaster.broadcast('login', this.appUser);
       this.form = null;
   }

    /**
     *
     * @param form
     */
    saveUserData(form:any) {

        this.show = true;
        this.removeMessage = false;

        if (this.type == 'profile') {

            let birthday:any;

            // generate birthday value
            if(form.day!=0 && form.month!=0 && form.year!=0) {
                birthday = form.year+'/'+form.month+'/'+form.day;
            } else {
                birthday = null;
            }

            let firstPassword = form['password'];
            let secondPassword = form['plainPassword'];

            // add birthday in form data
            form['birthDate'] = birthday;

            delete form.plainPassword;
            delete form.password;

            // remove day,month,year
            delete form.day;
            delete form.year;
            delete form.month;

            if(firstPassword && secondPassword) {
                this.plainPassword.first = firstPassword;
                this.plainPassword.second = secondPassword;
                form.plainPassword = this.plainPassword;
            }

            this._projectService.saveUserData(form)
                .subscribe(
                    (data) => {
                        this.saveMessage = true;
                        this.errorMessage = null;
                        this.refreshUserAndForm(data);
                        this.initProfileForm();
                        this.show = false;

                        setTimeout(() => {
                            this.saveMessage = null;
                        }, 4000);

                    },
                    error => {
                        this.errorMessage = JSON.parse(error._body);
                        this.show = false;
                    }
                );
        }

        if(this.type == 'notification') {

            this._projectService.postNotifySettings(form)
                .subscribe(
                    () => {
                        this.saveMessage = true;
                        this.show = false;

                        setTimeout(() => {
                            this.saveMessage = null;
                        }, 4000);

                    },
                    error => {
                        this.errorMessage = error._body;
                    }
                );
        }
    }

    /**
     *
     * @param user
     */
    checkEmailToken(user)
    {
        let emailKey = Object.keys(user.user_emails);

        for(let key of emailKey)
        {
            let emailsData = this.appUser.user_emails[key];

            if(emailsData && emailsData.token) {
                this.token = false;
                break;
            }
        }
    }

    /**
     *
     * @param email
     * @param secret
     */
    activationUserAddEmail(secret, email)
    {
        this._projectService.activationUserAddEmail(secret, email)
            .subscribe(
                () => {
                    this.router.navigate(['/ideas']);
                },
                error => {
                    this.errorMessage = error._body;

                    console.log(this.errorMessage);
                    if(this.errorMessage) {
                        this.broadcaster.broadcast('error', this.errorMessage);
                        this.router.navigate(['/error']);
                    }
                }
            );
    }

    /**
     * This function is used to get notify settings data
     *
     */
    getNotifySettings() {

    this._projectService.getNotifySettings()
        .subscribe(
            (data) => {
                this.notifySettings = data;
                this.ready = true;
                this.initNotifyForm();
                this.errorMessage = null;
                setTimeout(() =>{
                    this.loading = true;
                },200)
            },
            error => {
                this.errorMessage = error._body;
            }
        );
    }

    /**
     *
     * @param email
     */
  removeEmail(email:string) {

       this.show = true;

        this._projectService.removeUserEmail(email)
            .subscribe(
                (data) => {
                    this.removeMessage = true;
                    this.refreshUserAndForm(data);
                    this.initProfileForm();
                    this.show = false;

                    setTimeout(() => {
                        this.removeMessage = null;
                    }, 3000);
                },
                error => {
                    this.errorMessage = error;
                }
            );
    }
    removeProfile(){
        let config = new MdDialogConfig();
        config.viewContainerRef = this.viewContainerRef;
        this.removeProfileModal = this.dialog.open(RemoveProfileComponent, config);
        this.removeProfileModal.componentInstance.appUser = this.appUser;
        
        this.removeProfileModal.afterClosed().subscribe(result => {
        });
    }
}
