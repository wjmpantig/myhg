<template>
   <div>
      <h1 class="title">Edit user</h1>
		<h2 class="subtitle">{{user.last_name}} {{user.first_name}}</h2>
      <form @submit.prevent="updateUser()">
         <div class="field">
            <label for="" class="label">Last name</label>
            <div class="control">
               <input type="text"  :class="{'input':true,'is-danger':hasError('last_name')}" v-model="last_name" :disabled="loading">
            </div>
            <p class="help is-danger" v-show="hasError('last_name')">{{getError('last_name')}}</p>
         </div>
         <div class="field">
            <label for="" class="label">First name</label>
            <div class="control">
               <input type="text"  :class="{'input':true,'is-danger':hasError('first_name')}" v-model="first_name" :disabled="loading">
            </div>
            <p class="help is-danger" v-show="hasError('first_name')">{{getError('first_name')}}</p>
         </div>
         <div class="field">
            <label for="" class="label">Email</label>
            <div class="control">
               <input type="text" :class="{'input':true,'is-danger':hasError('email')}" v-model="email" :disabled="loading">
            </div>
            <p class="help is-danger" v-show="hasError('email')">{{getError('email')}}</p>
         </div>
         <div class="field">
            <label for="" class="label">Update Password</label>
            <div class="control">
               <input type="password" :class="{'input':true,'is-danger':hasError('password')}" v-model="password" :disabled="loading">
            </div>
            <p class="help is-danger" v-show="hasError('password')">{{getError('password')}}</p>
         </div>
         <div class="field">
            <div class="control">
               <button type="submit" class="button is-primary">Save changes</button>
            </div>
         </div>
         
      </form>
	</div>
</template>
<script>
   export default{
      data(){
         return {
            user: {},
            last_name:null,
            first_name:null,
            email:null,
            password: null,
            errors: [],
            loading: false,
         }
      },
      mounted(){
         this.loadUser();
      },
      methods:{
         loadUser(){
            this.loading = true;
            axios.get('/api/users/'+this.$route.params.id).then((response)=>{
               const user = this.user = response.data;
               this.last_name = user.last_name;
               this.first_name = user.first_name;
               this.email = user.email;
               this.loading = false;
               this.errors = [];
            }).catch(err=>{
               this.errors = err.response.data;
               this.loading = false;
            });
         },
         updateUser(){
            this.loading = true;
            let data = {
               last_name: this.last_name,
               first_name: this.first_name,
               email: this.email
            }
            if(!!this.password){
               data.password = this.password;
            }
            axios.post('/api/users/'+this.$route.params.id,data).then((response)=>{
               const user = this.user = response.data;
               this.last_name = user.last_name;
               this.first_name = user.first_name;
               this.email = user.email;
               this.password = null;
               this.loading = false;
            }).catch((err)=>{
               this.errors = err.response.data;
               this.loading = false;
            });
         },
         hasError(field){
				let error= this.errors;
				if(error.errors &&  error.errors[field]){
					return true;
				}
				return false;
			},
			getError(field){
				let error= this.errors;
				if(error.errors && error.errors[field]){
					if(Array.isArray(error.errors[field]) && error.errors[field].length == 1){
						return error.errors[field][0];
					}
					return error.errors[field];
				}
				return null;
			},
      }

   }
</script>