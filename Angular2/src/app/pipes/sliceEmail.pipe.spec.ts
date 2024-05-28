import { async, inject, TestBed } from '@angular/core/testing';
import { SliceEmailPipe } from './sliceEmail.pipe';

fdescribe('Pipe: RemoveTagPipe', () => {
    let pipe;

    //setup
    beforeEach(async(() => {

            TestBed.configureTestingModule({
                providers: [ SliceEmailPipe ]
            })
                .compileComponents();
        }
    ));

    beforeEach(inject([SliceEmailPipe], p => {
        pipe = p;
    }));

    it('should work with empty string', () => {
        expect(pipe.transform('')).toEqual('');
    });

    it('should slice email', () => {
        expect(pipe.transform('test@test.com')).toEqual('...@test.com');
    });

});