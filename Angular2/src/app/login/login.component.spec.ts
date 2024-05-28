import { async, inject, ComponentFixture, TestBed, fakeAsync, tick } from '@angular/core/testing';
import { TranslateService, TranslateLoader, TranslateModule, TranslateParser } from 'ng2-translate';
import { MaterialModule } from '@angular/material';
import { CacheService } from 'ng2-cache/ng2-cache';
import { Broadcaster } from '../tools/broadcaster';
import 'rxjs/add/operator/map';
import { FormsModule, ReactiveFormsModule }  from '@angular/forms';
import { LoginComponent } from './login.component';
import { ProjectService } from '../project.service';
import { RouterTestingModule } from '@angular/router/testing';
import { RouterModule, Routes } from '@angular/router';
import { ɵh } from '@angular/material/typings'

// import { AngularFire } from 'angularfire2';
import { AngularFireModule } from 'angularfire2';
// import { AuthProviders } from 'angularfire2';
// import { AuthMethods } from 'angularfire2';

import { BaseRequestOptions } from '@angular/http';
import { MockBackend } from '@angular/http/testing';

const LoginRoute: Routes = [
    { path: '',  component: LoginComponent }
];

// Must export the config
export const firebaseConfig = {
    apiKey: "AIzaSyDS4TuFB7Uj-M0exn1qWHVpaUhUwwKanlQ",
    authDomain: "bucketlist-f143c.firebaseapp.com",
    databaseURL: "https://bucketlist-f143c.firebaseio.com",
    storageBucket: "bucketlist-f143c.appspot.com",
    messagingSenderId: "264286375978"
};
// const myFirebaseAuthConfig = {
//     provider: AuthProviders.Google,
//     method: AuthMethods.Popup
// };

fdescribe('LoginComponent', () => {

        //set variables
        let component: LoginComponent;
        let fixture: ComponentFixture<LoginComponent>;
        let user:any = {};

        beforeEach(async(() => {

                TestBed.configureTestingModule({
                    declarations: [LoginComponent],
                    providers: [ProjectService, ɵh, MockBackend, BaseRequestOptions, CacheService, TranslateService, TranslateLoader, TranslateParser, Broadcaster],
                    imports: [MaterialModule,  AngularFireModule.initializeApp(firebaseConfig), TranslateModule,
                        RouterModule, FormsModule, ReactiveFormsModule, RouterTestingModule.withRoutes(LoginRoute)],
                })
                    .compileComponents();
            }
        ));

        beforeEach(() => {
            fixture = TestBed.createComponent(LoginComponent);
            component = fixture.componentInstance;
            fixture.detectChanges();
        });

        beforeEach(inject([ProjectService], (projectService: ProjectService) => {
            projectService.initPaths('http://behat.bucketlist.loc/');
        }));

        //check login user functionality
        it('Authorization user', inject([ProjectService, CacheService, Broadcaster], (projectService, cacheService, broadcast) => {

            let loginData = {
                username: 'user1@user.com',
                password: 'Test1234',
                apikey: true
            };

            projectService.auth(loginData).subscribe((res) => {

                if (res.apiKey) {
                    localStorage.setItem('apiKey', res.apiKey);
                }

                this.user = res.userInfo;

                cacheService.set('user_',  this.user, {maxAge: 3 * 24 * 60 * 60});
                broadcast.broadcast('getUser', this.user);

                expect(this.user.first_name).toEqual('user1');
                expect(this.user.last_name).toEqual('useryan');
             }
        );
        }
    ));
    }
);
