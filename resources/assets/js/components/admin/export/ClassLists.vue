<template>
	<div>
		<form @submit.prevent="submit()">
			<div class="field">
				<label for="season" class="label">Season</label>
				<div class="control">
					<div class="select" name="season">
						<select v-model="season">
							<option v-for="season in seasons" :value="season.id">{{season.name}}</option>
						</select>
					</div>
					
				</div>
			</div>
			<div class="field is-grouped">
				<div class="control">
					<button class="button is-primary" type="submit" :disabled="loading">
						{{ loading ? "" : "Export" }}
						<font-awesome-icon :icon="['fas','spinner']" class="fa-spin" v-show="loading"></font-awesome-icon>
						
					</button>
					
				</div>
				<div class="control">
					<a class="button is-info" :href="fileLink" v-show="file && !loading">Download</a>
				</div>
			</div>
		</form>
	</div>	
</template>
<script>
	export default{
		data(){
			return {
				seasons:[],
				season:null,
				file:null,
				loading:false,
			
			}
		},
		computed:{
			fileLink(){
				return this.file == null ? '/#' : '/export/' +this.file.id;
			}
		},
		mounted(){
			this.loadSeasons();
		},
		methods:{
			loadSeasons(){
				axios.get('/api/seasons').then(response=>{
					this.seasons = response.data;
					this.season = this.seasons[0].id;
					// this.loadSections();
				});
			},
			
			submit(){
				
				this.loading = true;
				let data = {
					id: this.season,
					
				}
				axios.post('/api/export/class-lists',data).then(response=>{
					this.file=response.data;
				}).catch(error=>{
					console.error(error);
				}).then(()=>{
					this.loading = false;
				});
			}
		}
	}
</script>
