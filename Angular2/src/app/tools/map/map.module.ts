import { NgModule } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { TranslateModule} from 'ng2-translate';
import { RouterModule } from '@angular/router';
import { AgmCoreModule } from "angular2-google-maps/core";

import { MapComponent } from './map.component';
import { AutocomplateMapComponent } from './autocomplate-map.component';

@NgModule({
  imports: [
    AgmCoreModule,
    CommonModule,
    TranslateModule,
    RouterModule,
    FormsModule,
    ReactiveFormsModule,
  ],
  declarations: [
    MapComponent,
    AutocomplateMapComponent
  ],
  exports: [
    MapComponent,
    AutocomplateMapComponent
  ]
})
export class MapModule { }
