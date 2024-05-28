import { NgModule } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { TranslateModule} from 'ng2-translate';
import { RouterModule } from '@angular/router';
import { ComponentModule} from '../components/components.module';
import { MaterialModule } from '@angular/material';
import { ShareButtonsModule} from "ng2-sharebuttons";
import { SwiperModule } from 'angular2-useful-swiper';

import { ConfirmComponent } from './confirm/confirm.component';
import { RemoveProfileComponent } from './remove-profile/remove-profile.component';
import { ShareComponent } from './share/share.component';
import { LightboxComponent } from './lightbox/lightbox.component';

@NgModule({
    imports: [
        CommonModule,
        TranslateModule,
        ComponentModule,
        RouterModule,
        FormsModule,
        ShareButtonsModule,
        SwiperModule,
        MaterialModule.forRoot()
    ],
    declarations: [
        ConfirmComponent,
        RemoveProfileComponent,
        ShareComponent,
        LightboxComponent
    ],
    entryComponents: [
        ConfirmComponent,
        RemoveProfileComponent,
        ShareComponent
    ],
    exports: [
        ConfirmComponent,
        RemoveProfileComponent,
        ShareComponent,
        LightboxComponent
    ],


})
export class ModalsModule { }
