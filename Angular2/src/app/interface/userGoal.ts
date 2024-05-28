import {Goal} from "./goal";
import {User} from "./user";

export interface UserGoal {
    id: number,
    status: number,
    goal: Goal,
    user: User,
    date_status?: number,
    do_date?:any,
    steps?:any,
    formatted_steps?:any,
    completion_date?:any
}
