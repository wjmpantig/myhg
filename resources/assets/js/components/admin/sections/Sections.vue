<template>
	<div>
		<h2 class="title">Sections</h2>

		<table class="table is-bordered is-striped is-hoverable">
			<thead>
				<tr>
					<th>Name</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="(section,index) in sections">
					<td>
						<span v-show="!section.isEditable"><router-link :to="{path:'sections/' + section.id}">{{section.name}}</router-link></span>
						<form v-on:submit.prevent="updateSection(section,index)">
							
							<div class="field" v-show="section.isEditable">
								<input v-bind:ref="'name_' + section.id" class="input" type="text" placeholder="name" v-model="section.name" :disabled="section.isUpdating" @blur="updateSection(section,index)">
								<p class="help is-danger" v-show="section.errors">
									{{section.errors ? section.errors.errors['name'][0] : ""}}
								</p>								
							</div>	
							<input type="hidden">
						</form>
					</td>
					<td> 
						<div v-show="!section.isEditable">
							<a href="#" v-on:click="toggleEditable(section,index)"><font-awesome-icon :icon="['far','edit']"></font-awesome-icon></a>
							<a href="#" class="has-text-danger" @click="confirmDeleteSection(section,index)"><font-awesome-icon :icon="['far','trash-alt']"></font-awesome-icon></a>
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
			season: null,
			sections: [],
		}
	},
	mounted(){

		// axios.get('api/seasons/').then(response=>{
		// 	console.log(response)
		// }).catch(err=>{
		// 	console.error(err);
		// });
		axios.get('api/sections/').then(response=>{
			this.sections = response.data;
		}).catch(err=>{
			console.error(err);
		});
	},
	methods:{
		toggleEditable(section,index){
			Vue.set(section,'isEditable',!section.isEditable);
			// section.isEditable = !section.isEditable;
			let el = this.$refs['name_' + section.id][0];
			
			Vue.nextTick(()=>{
				el.focus();
			})
		},
		updateSection(section,index){
			Vue.set(section,'isUpdating', true);
			let sections = this.sections;
			axios.post('api/sections/'+section.id,section).then(response=>{
				console.log(response.data);
				Vue.set(sections,index,response.data)
			}).catch(err=>{
				Vue.set(section,'errors',err.response.data);
			}).then(()=>{
				Vue.set(section,'isUpdating', false);
			})
			// Vue.set(section,'isUpdating', false);
		},
		confirmDeleteSection(section,index){
			this.$dialog.confirm('Confirm delete section:\"'+section.name+'\"?',{
				loader:true
			}).then((dialog)=>{
				this.deleteSection(section,index,dialog);
			});
		},
		deleteSection(section,index,dialog){
			let sections = this.sections;
			axios.delete('api/sections/'+section.id,section).then(response=>{
				sections.splice(index,1);
				dialog.close();
			}).catch(err=>{
				console.error(err);
				dialog.close();
				let error = err.response.data;
				let message = error.message ? error.message : error;
				this.$dialog.alert('Error: ' + message);
			})
		}
	}
}
</script>