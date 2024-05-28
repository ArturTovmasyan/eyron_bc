import {Goal} from "./goal";
import {User} from "./user";

export interface Activity {
    id: number,
    user: User,
    goals: Goal[],
    reserveGoals?: Goal[],
    datetime?: any,
    forTop?: boolean,
    forBottom?: boolean,
    createComment?: boolean,
    showComment?: boolean
}