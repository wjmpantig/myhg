<template>
	<div>
		<h2 class="title">{{user.last_name}} {{user.first_name}}</h2>

		<div class="field">
			<label for="" class="label">Last name</label>
			<div class="control">
				<input type="text" class="input" v-model="user.last_name">
			</div>
		</div>
		<div class="field">
			<label for="" class="label">First name</label>
			<div class="control">
				<input type="text" class="input" v-model="user.first_name">
			</div>
		</div>
	</div>
</template>
<script>
	export default{
		data(){
			return {
				user:{}
			};
		},
		mounted(){
			axios.get('/api/students/'+this.$route.params.id).then(response=>{
				
				this.user = response.data;
			});
		},

	}
</script>