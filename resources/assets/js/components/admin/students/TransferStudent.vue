<template>
	<div>
		<h2 class="title">Transfer student</h2>
		<div class="subtitle">{{user.last_name}} {{user.first_name}}</div>
		<div class="field">
			<label for="" class="label">Transfer from:</label>
			<div class="control">
				<div class="select">
					<select v-model="section" @change="">
						<option v-for="section in user.sections" :value="section.id">{{section.season_name}} season - {{section.name}}</option>
					</select>
				</div>
			</div>
		</div>
		<div class="field">
			<label for="" class="label">Transfer to:</label>
			<div class="control">
				<div class="select">
					<select v-model="target_section" @change="">
						<option v-for="section in sections" :value="section.id">{{section.name}}</option>
					</select>
				</div>
			</div>
		</div>
		
	</div>
</template>
<script>
	export default{
		data(){
			return {
				user:{},
				section:{},
				sections: [],
				target_section:{}
			};
		},
		mounted(){
			axios.get('/api/students/'+this.$route.params.id).then(response=>{
				
				this.user = response.data;
			});
		},
		methods:{
			loadSections(){
				axios.get('api/sections/',{
					params: {
						season_id:this.season
					}
				}).then(response=>{
					this.sections = response.data;
				}).catch(err=>{
					console.error(err);
				});
			}
		}

	}
</script>