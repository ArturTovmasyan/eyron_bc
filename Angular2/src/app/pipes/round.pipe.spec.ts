import { async, inject, TestBed } from '@angular/core/testing';
import { RoundPipe } from './round.pipe';

fdescribe('Pipe: RoundPipe', () => {
    let pipe;

    //setup
    beforeEach(async(() => {

            TestBed.configureTestingModule({
                providers: [ RoundPipe ]
            })
                .compileComponents();
        }
    ));

    beforeEach(inject([RoundPipe], p => {
        pipe = p;
    }));


    it('should round integer', () => {
        expect(pipe.transform(5.555)).toEqual(5);
    });

});