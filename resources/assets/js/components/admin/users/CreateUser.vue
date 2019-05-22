<template>
	<div>
		<h2 class="title">Create User</h2>
		<form @submit.prevent="submit()">
			<div class="field">
				<label for="" class="label">Last name</label>
				<div class="control">
					<input type="text"  :class="{'input':true,'is-danger':hasError('last_name')}" v-model="last_name" >
				</div>
				<p class="help is-danger" v-show="hasError('last_name')">{{getError('last_name')}}</p>
			</div>
			<div class="field">
				<label for="" class="label">First name</label>
				<div class="control">
					<input type="text"  :class="{'input':true,'is-danger':hasError('first_name')}" v-model="first_name">
				</div>
				<p class="help is-danger" v-show="hasError('first_name')">{{getError('first_name')}}</p>
			</div>
			<div class="field">
				<label for="" class="label">Email</label>
				<div class="control">
					<input type="text" :class="{'input':true,'is-danger':hasError('email')}" v-model="email" >
				</div>
				<p class="help is-danger" v-show="hasError('email')">{{getError('email')}}</p>
			</div>
			
			<div class="field">
				<label for="" class="label">New password</label>
				<div class="control">
					<input type="password"  :class="{'input':true,'is-danger':hasError('password')}" v-model="password">
				</div>
				<p class="help is-danger" v-show="hasError('password')">{{getError('password')}}</p>
			</div>
			<div class="field">
				<label for="" class="label">Repeat new password</label>
				<div class="control">
					<input type="password"  :class="{'input':true,'is-danger':hasError('password_confirmation')}" v-model="password_confirmation">
				</div>
				<p class="help is-danger" v-show="hasError('password_confirmation')">{{getError('password_confirmation')}}</p>
			</div>
			<div class="field">
				<label for="type" class="label">User type</label>
				<div class="control">
					<div class="select" name="type">
						<select v-model="type">
							<option v-for="type in types" :value="type.id">{{type.name}}</option>
						</select>
					</div>
					
				</div>
				<p class="help is-danger" v-show="hasError('type')">{{getError('type')}}</p>
			</div>
			<button class="button is-primary" type="submit">Create User</button>
		</form>
	</div>
</template>
<script>
	export default{
		data(){
			return {
				user:{},
				first_name:null,
				last_name:null,
				email:null,
				password:null,
				password_confirmation:null,
				types:[],
				type:{},
				errors:{}
			};
		},
		mounted(){
			axios.get('/api/user_types').then(response=>{
				this.types = response.data;
				this.type = this.types[0].id;
				console.log(this.type)
			}).catch(error=>{
				console.error(error.response);
			})
		},
		methods:{
			hasError(field){
				let errors = this.errors;
				if(errors && errors[field]){
					return true;
				}
				return false;
			},
			getError(field){
				let errors= this.errors;
				if(errors && errors[field]){
					if(Array.isArray(errors[field]) && errors[field].length == 1){
						return errors[field][0];
					}
					return errors[field];
				}
				return null;
			},
			submit(){
				this.errors = {};
				let data = {
					first_name: this.first_name,
					last_name:this.last_name,
					email:this.email,
					password: this.password,
					password_confirmation:this.password_confirmation,
					type: this.type
				}
				axios.post('api/users',data).then(response=>{
					console.log(response.data);
					//redirect
					this.$router.push({path:"/admin/users/"});
				}).catch(error=>{
					this.errors = error.data.errors;
				})
			}
		}
	}
</script>