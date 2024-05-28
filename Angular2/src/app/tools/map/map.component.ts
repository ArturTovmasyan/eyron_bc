import { Component, OnInit , NgZone, ViewChild, ViewEncapsulation, ElementRef, Input} from '@angular/core';
import { Router } from '@angular/router';

import { FormControl } from "@angular/forms";
import { MapsAPILoader } from 'angular2-google-maps/core';
import { Marker } from '../../interface/marker';
import { Location } from '../../interface/location';
import { Broadcaster } from '../broadcaster';
import {CacheService, CacheStoragesEnum} from 'ng2-cache/ng2-cache';

declare var google;
@Component({
  selector: 'map-single',
  templateUrl: './map.component.html',
  styleUrls: ['./map.component.less']
})
export class MapComponent implements OnInit {
  @Input() locations: Location[];
  public latitude: number;
  public longitude: number;
  public activeGoalMarkerIcon1: string = "assets/images/active-icon.svg";
  public activeGoalMarkerIcon2: string = "assets/images/completed-icon.svg";
  public passiveMarkerIcon: string = "assets/images/map-marker-purple.svg";
  public activeMarkerIcon: string = "assets/images/map-marker-purple.svg";
  public searchControl: FormControl;
  public zoom: number;
  public notAllowed: boolean = true;
  public autocomplete: any;
  public bounds: any;
  public markers: Marker[];

  @ViewChild("search")
  public searchElementRef: ElementRef;

  constructor(
      private _cacheService: CacheService,
      private mapsAPILoader: MapsAPILoader,
      private ngZone: NgZone,
      private router:Router,
      private broadcaster: Broadcaster
  ) {}

  ngOnInit() {
    //set google maps defaults
    this.zoom = 4;
    this.latitude = 39.8282;
    this.longitude = -98.5795;

    //create search FormControl
    this.searchControl = new FormControl();

    this.mapsAPILoader.load().then(() => {
      this.bounds = new google.maps.LatLngBounds(null);
      if(this.locations){
        for(let location of this.locations){
          if(this.locations.length > 1){
            this.bounds.extend({
              lat: location.latitude,
              lng: location.longitude
            });
          } else {
            this.zoom = 15;
            this.latitude = location.latitude;
            this.longitude = location.longitude;
          }

        }

      }
    });

    this.broadcaster.on<Location[]>('getLocation')
        .subscribe(locations => {
          this.bounds = new google.maps.LatLngBounds(null);
          for (let location of locations){
            this.bounds.extend(location);
          }
        });

    this.broadcaster.on<string>('addGoal')
        .subscribe(data => {
          // if(scope.mapMarkers[data] && scope.mapMarkers[data].map){
          //     var icon = {
          //         url: this.activeGoalMarkerIcon1,
          //         scaledSize:new google.maps.Size(35, 50)
          //     };
          //     scope.mapMarkers[data].setIcon(icon);
          // }
        });

    this.broadcaster.on<string>('lsJqueryModalClosedSaveGoal')
        .subscribe(userGoal => {
          // if(!userGoal || !userGoal.status || !scope.mapMarkers[userGoal.goal.id] || !scope.mapMarkers[userGoal.goal.id].map)
          //         return;
          //
          //     var icon = {
          //         url: scope['activeGoalMarkerIcon'+userGoal.status],
          //         scaledSize:new google.maps.Size(35, 50)
          //     };
          //     scope.mapMarkers[userGoal.goal.id].setIcon(icon);
        });

    this.broadcaster.on<string>('doneGoal')
        .subscribe(data => {
          // if(scope.mapMarkers[data] && scope.mapMarkers[data].map){
          //     var icon = {
          //         url: scope.activeGoalMarkerIcon2,
          //         scaledSize:new google.maps.Size(35, 50)
          //     };
          //     scope.mapMarkers[data].setIcon(icon);
          // }
        });

  }

  clickMarker(marker){
    this.router.navigate(['/goal/'+marker.slug]);
  }
}
