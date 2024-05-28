import { async, inject, ComponentFixture, TestBed, fakeAsync, tick } from '@angular/core/testing';
import {Http, ConnectionBackend, BaseRequestOptions, Response, ResponseOptions} from '@angular/http';
import { By } from '@angular/platform-browser';
import { DebugElement, OnInit, Component, NO_ERRORS_SCHEMA } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { FormsModule, ReactiveFormsModule }  from '@angular/forms';
import { TranslateService, TranslateLoader, TranslateModule, TranslateParser } from 'ng2-translate';
import { MaterialModule } from '@angular/material';
import { RouterTestingModule } from '@angular/router/testing';
import { CacheService } from 'ng2-cache/ng2-cache';
import { Broadcaster } from '../tools/broadcaster';
import { Uploader } from 'angular2-http-file-upload';
import {MockBackend} from '@angular/http/testing';
import 'rxjs/add/operator/map';

import { ProfileHeaderComponent } from '../block/profile-header/components/profile-header.component';
import { ControlMessagesComponent } from '../components/control-messages/control-messages.component';
import { SettingsComponent } from './settings.component';

import { RoundPipe } from '../pipes/round.pipe';

import { ProjectService } from '../project.service';
import { ValidationService } from '../validation.service';

const SettingsRoutes: Routes = [
    { path: '',  component: SettingsComponent },
    { path: ':type',  component: SettingsComponent },
    { path: ':type/:secret/:addMail',  component: SettingsComponent }
];

fdescribe('SettingsComponent', () => {

        let component: SettingsComponent;
        let fixture: ComponentFixture<SettingsComponent>;

        beforeEach(async(() => {

                TestBed.configureTestingModule({
                    declarations: [ SettingsComponent, ProfileHeaderComponent, ControlMessagesComponent, RoundPipe],
                    providers: [ValidationService, ProjectService, CacheService, Uploader, TranslateService, TranslateLoader, TranslateParser, Broadcaster],
                    imports: [MaterialModule, TranslateModule, FormsModule, ReactiveFormsModule, RouterModule, RouterTestingModule.withRoutes(SettingsRoutes)],
                    schemas: [NO_ERRORS_SCHEMA]
                })
                    .compileComponents();
            }
        ));

        beforeEach(() => {
            fixture = TestBed.createComponent(SettingsComponent);
            component = fixture.componentInstance;
            fixture.detectChanges();
        });

        // beforeEach(inject([ProjectService], (projectService: ProjectService) => {
        //     projectService.initPaths('http://behat.bucketlist.loc/');
        // }));

        it('Get User', inject([CacheService], (cache) => {
            let appUser = cache.get('user_');
        }));
    }
);
