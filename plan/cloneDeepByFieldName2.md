# Clone Deep By Field Name 2

As an improvement from current method `entityCloneDeepByFieldName`, it is required to have the following improvement: register from other tables that references the given id will be written as a copy. By those new registers itself may be have relationship in other tables. Those new method also needs to fetches data, replicating those related data from other tables. Notice: It is good to track the cloned ids. Going deep in the relationships may fetch ids from tables that already has been copyied.
