<template>
	<div>
		<h2 class="title">Settings</h2>
		<form @submit.prevent="submit()">
			<div class="field">
				<label for="" class="label">Current Password</label>
				<div class="control">
					<input type="password"  :class="{'input':true,'is-danger':hasError('current_password')}" v-model="current_password" >
				</div>
				<p class="help is-danger" v-show="hasError('current_password')">{{getError('current_password')}}</p>
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
			<button class="button is-primary" type="submit">Change password</button>
			
		</form>
		<div class="message is-primary" v-show="success">
			<div class="message-body">
				Password updated!
			</div>
		</div>
	</div>
</template>
<script>
	export default{
		data(){
			return{
				current_password: null,
				password: null,
				password_confirmation: null,
				errors:{},
				success: false,
			}
		},
		mounted(){

		},
		methods:{
			hasError(field){
				let errors= this.errors;
				if(errors &&  errors[field]){
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
				axios.post('/api/user/password',{
					current_password: this.current_password,
					password : this.password,
					password_confirmation:this.password_confirmation
				}).then(response=>{
					this.success = true;
				}).catch(errors=>{
					console.log(errors);
					this.errors = errors.data.errors;
				});
			}
		}
	}
</script>