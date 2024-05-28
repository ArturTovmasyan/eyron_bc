/* tslint:disable:no-unused-variable */
import { async, ComponentFixture, TestBed } from '@angular/core/testing';
import { By } from '@angular/platform-browser';
import { DebugElement } from '@angular/core';
import { InfiniteScrollModule } from 'angular2-infinite-scroll';
import { TranslateModule} from 'ng2-translate';
import { ComponentModule } from '../components/components.module';
import { ActivityBlockModule } from '../block/activityBlock.module';
import { RouterTestingModule} from "@angular/router/testing";
import { Broadcaster} from '../tools/broadcaster';
import {CacheService, CacheStoragesEnum} from 'ng2-cache/ng2-cache';
import { TranslateService,TranslateLoader, TranslateParser} from 'ng2-translate';


import { NotificationComponent } from './notification.component';

describe('NotificationComponent', () => {
  let component: NotificationComponent;
  let fixture: ComponentFixture<NotificationComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ NotificationComponent ],
      imports: [
        InfiniteScrollModule,
        TranslateModule,
        ComponentModule,
        ActivityBlockModule,
        RouterTestingModule
      ],
      providers:[
        Broadcaster,
        CacheService,
        TranslateService,
        TranslateLoader,
        TranslateParser
      ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(NotificationComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  fit('should create', () => {
    expect(component).toBeTruthy();
  });
});
