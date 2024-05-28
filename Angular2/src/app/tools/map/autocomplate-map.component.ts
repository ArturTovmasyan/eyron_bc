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
  selector: 'map-autocomplate',
  templateUrl: './autocomplate-map.component.html',
  styleUrls: ['./map.component.less']
})
export class AutocomplateMapComponent implements OnInit {
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

    //set current position
    this.setCurrentPosition();

    //load Places Autocomplete
    this.mapsAPILoader.load().then(() => {
      this.autocomplete = new google.maps.places.Autocomplete(this.searchElementRef.nativeElement, {
        types: []
      });
      this.bounds = new google.maps.LatLngBounds(null);
      this.autocomplete.addListener("place_changed", () => {
        this.ngZone.run(() => {
          //get the place result :google.maps.places.PlaceResult
          let place: any = this.autocomplete.getPlace();

          let marker:Marker = {
            latitude: place.geometry.location.lat(),
            longitude: place.geometry.location.lng(),
            iconUrl: this.passiveMarkerIcon,
            title: this.searchElementRef.nativeElement.value
          };

          this.broadcaster.broadcast('location_changed', marker);

          this.markers = [marker];
          this.latitude = place.geometry.location.lat();
          this.longitude = place.geometry.location.lng();
          this.bounds.extend({
            lat: this.latitude,
            lng: this.longitude
          });
          this.zoom = 10;
        });
      });
    });

    this.broadcaster.on<Location[]>('getLocation')
        .subscribe(locations => {
          this.bounds = new google.maps.LatLngBounds(null);
          for (let location of locations){
            this.bounds.extend(location);
          }
        });

    this.broadcaster.on<any>('addGoal')
        .subscribe(data => {
          this.changeLocationIcon(data.goal.id, 1);
        });
    this.broadcaster.on<any>('removeGoal')
        .subscribe(id => {
          this.changeLocationIcon(id, 0);
        });

    this.broadcaster.on<any>('saveGoal')
        .subscribe(userGoal => {
          this.changeLocationIcon(userGoal.goal.id, userGoal.status);
        });

    this.broadcaster.on<any>('doneGoal')
        .subscribe(data => {
          if(data.userGoal && data.userGoal.goal){
            this.changeLocationIcon(data.userGoal.goal.id, 2);
          }
        });

  }

  changeLocationIcon(id, status){
    for(let location of this.locations){
      if(location.id == id){
        location.status = status
      }
    }
  }
  
  setType(types){
    this.autocomplete.setTypes(types)
  }

  setPosition(position){
    this.latitude = parseFloat(position.coords.latitude);
    this.longitude = parseFloat(position.coords.longitude);
    let marker:Marker = {
      latitude: this.latitude,
      longitude: this.longitude,
      iconUrl: this.passiveMarkerIcon,
      title: "Your Position"
    };

    this.broadcaster.broadcast('location_changed', marker);
    this.markers = [marker];
    this.notAllowed = false;
    this.zoom = 10;

    this.bounds.extend({
      'latitude': this.latitude,
      'longitude': this.longitude
    });
  }

  clickMarker(marker){
    this.router.navigate(['/goal/'+marker.slug]);
  }

  private setCurrentPosition() {
    let position = this._cacheService.get('location');
    if(position && position.coords){
      this.setPosition(position);
    }else {
      if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(this.getposBind,() =>{
          this.notAllowed = false;
        });
      }
    }
  }
  getpos(position){
      this.notAllowed = false;
      this.setPosition(position);
      this._cacheService.set('location', position, {maxAge: 3 * 24 * 60 * 60});
  };

  getposBind = this.getpos.bind(this);


}
