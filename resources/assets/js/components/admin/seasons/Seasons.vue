<template>
	<div>
		<h2 class="title">Seasons</h2>
      <form @submit.prevent="createSeason()" :disabled="loading">
         <div class="field has-addons">
            <div class="control">
               <input type="text" class="input" v-model="season.name" placeholder="New season name.." :disabled="loading" required/>
            </div>
            <div class="control">
               <button class="button is-primary" type="submit">Add new season</button>
            </div>
         </div>
      </form>
		<table class="table is-bordered is-striped is-hoverable">
			<thead>
				<tr>
					<th>Name</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="(season,index) in seasons">
					<td>
						<span v-show="!season.isEditable">{{season.name}}</span>
						<form v-on:submit.prevent="updateSeason(season,index)">
							
							<div class="field" v-show="season.isEditable">
								<input v-bind:ref="'name_' + season.id" class="input" type="text" placeholder="name" v-model="season.name" :disabled="season.isUpdating" @blur="updateSeason(season,index)">
								<p class="help is-danger" v-show="season.errors">
									{{season.errors ? season.errors.errors['name'][0] : ""}}
								</p>								
							</div>	
							<input type="hidden">
						</form>
					</td>
					<td class="has-text-centered"> 
						<div v-show="!season.isEditable">
							<a href="#" @click.prevent="toggleEditable(season,index)"><font-awesome-icon :icon="['far','edit']"></font-awesome-icon></a>
							<a href="#" class="has-text-danger" @click.prevent="confirmDeleteSeason(season,index)"><font-awesome-icon :icon="['far','trash-alt']"></font-awesome-icon></a>
						</div>
						
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</template>
<script>
export default{
	data(){
		return {
			season: {
            name: null
         },
			seasons: [],
         loading: false,
		}
	},
	mounted(){
		this.loadSeasons();
	},
	methods:{
		loadSeasons(){
			this.seasons = [];
			axios.get('api/seasons/').then(response=>{
				this.seasons = response.data;
			}).catch(err=>{
				console.error(err);
			});
		},
      createSeason(){
         this.loading = true;
         axios.put('api/seasons',this.season).then(response=>{
            console.log(this.season);
            this.seasons.splice(0,0,response.data);
            this.loading = false;
            this.season.name = null;
         }).catch(err=>{
            this.loading = false;
         });
      },
		toggleEditable(season,index){
			Vue.set(season,'isEditable',!season.isEditable);
			// season.isEditable = !season.isEditable;
			let el = this.$refs['name_' + season.id][0];
			
			Vue.nextTick(()=>{
				el.focus();
			})
		},
		updateSeason(season,index){
			Vue.set(season,'isUpdating', true);
			let seasons = this.seasons;
			axios.post('api/seasons/'+season.id,season).then(response=>{
				console.log(response.data);
				Vue.set(seasons,index,response.data)
			}).catch(err=>{
				Vue.set(season,'errors',err.data);
			}).then(()=>{
				Vue.set(season,'isUpdating', false);
			})
			// Vue.set(season,'isUpdating', false);
		},
		confirmDeleteSeason(season,index){
			this.$dialog.confirm('Confirm delete season:\"'+season.name+'\"?',{
				loader:true
			}).then((dialog)=>{
				this.deleteSeason(season,index,dialog);
			});
		},
		deleteSeason(season,index,dialog){
			let seasons = this.seasons;
			axios.delete('api/seasons/'+season.id,season).then(response=>{
				seasons.splice(index,1);
				dialog.close();
			}).catch(err=>{
				console.error(err);
				dialog.close();
				let error = err.data;
				let message = error.message ? error.message : error;
				this.$dialog.alert('Error: ' + message);
			})
		}
	}
}
</script>