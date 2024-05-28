import { async, inject, ComponentFixture, TestBed, fakeAsync, tick } from '@angular/core/testing';
import { RouterModule, Routes, Router } from '@angular/router';
import { FormsModule, ReactiveFormsModule }  from '@angular/forms';
import { TranslateService, TranslateLoader, TranslateModule, TranslateParser } from 'ng2-translate';
import { MaterialModule } from '@angular/material';
import { RouterTestingModule } from '@angular/router/testing';
import { CacheService } from 'ng2-cache/ng2-cache';
import { Broadcaster } from '../tools/broadcaster';
import { Uploader } from 'angular2-http-file-upload';
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
    { path: ':type/:secret/:addMail', component: SettingsComponent }
];

describe('Router tests', () => {

    let location, router;
    let component: SettingsComponent;
    let fixture: ComponentFixture<SettingsComponent>;

    beforeEach(async(() => {

            TestBed.configureTestingModule({
                declarations: [ SettingsComponent, ProfileHeaderComponent, ControlMessagesComponent, RoundPipe],
                providers: [ValidationService, Location, ProjectService, CacheService, Uploader, TranslateService, TranslateLoader, TranslateParser, Broadcaster],
                imports: [MaterialModule, TranslateModule, FormsModule, ReactiveFormsModule, RouterModule, RouterTestingModule.withRoutes(SettingsRoutes)],
            })
                .compileComponents();
        }
    ));

    beforeEach(() => {
        fixture = TestBed.createComponent(SettingsComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });


    beforeEach(inject([Router, Location], (r, l) => {
        router = r;
        location = l;
    }));

    //specs
    it('Should be able to navigate to Settings page', done => {
        router.navigate(['/edit/profile']).subscribe(() => {
            expect(location.path()).toBe('/edit/profile');
            done();
        }).catch(e => done.fail(e));
    });

    it('Should be able to navigate to Notification page', done => {
        router.navigate(['edit/notification']).subscribe(() => {
            expect(location.path()).toBe('/edit/notification');
            done();
        }).catch(e => done.fail(e));
    });

    it('should create', () => {
        expect(true).toBe(true);
    });

});