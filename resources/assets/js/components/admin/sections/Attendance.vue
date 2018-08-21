<template>
	<div>
		<form @submit.prevent="addAttendance">
			<div class="field has-addons">
				<div class="control has-icons-left">
					<datepicker placeholder="New date" v-bind:input-class="{'input':true,'is-danger': new_date.error}" v-model="new_date.date" format="MMM dd yyyy"></datepicker>
					<span class="icon is-left">
						<font-awesome-icon :icon="['far','calendar-alt']"></font-awesome-icon>
					</span>
				</div>
				
				<div class="control">
					<button type="submit" class="button is-primary is-outlined">Add date</button>
				</div>
			</div>
			
		</form>
		
		<p class="help is-danger" v-show="new_date.error">
			{{new_date_error}}
		</p>
		<div class="table-wrapper" v-show="dates.length > 0">
			<table v-bind:class="{'table is-hoverable is-bordered is-striped': true,'is-fullwidth is-narrow':dates.length > 5}">				
				<thead>
					<tr>
						<th>Name</th>
						<th v-for="(date,index) in dates" class="has-text-centered">
							<div>{{date.date | moment("MM/DD")}}</div>
							<a @click="confirmDelete(date)" class="has-text-danger">
								<font-awesome-icon :icon="['fas','times']"></font-awesome-icon>
							</a>
						</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="(student,index) in students">
						<td>{{ student.last_name}}, {{ student.first_name}}</td>
						<td v-for="(date,index) in dates" class="has-text-centered">
							<input type="checkbox" v-model="student.attendance[date.id]" v-on:change="togglePresent(student.id,date.id,student.attendance[date.id])">
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	
</template>
<script>

export default{
	data(){
		return {
			dates:[],
			students:[],
			new_date: {
				date: null,
				error: null
			}
		}
	},
	mounted(){
		this.loadAttendance();
	},
	computed:{
		new_date_error(){

			return this.new_date.error == null ? '' : this.new_date.error.join(' ');
		}
	},
	methods:{
		togglePresent(student_id,section_attendance_id,value){
			axios.post('/api/sections/'+this.$route.params.id+'/attendance',
				{section_attendance_id,student_id,value}
				).then(response=>{
				console.log(response.data);
			}).catch(err=>{
				if(err.response){
					console.error(err.response.data.message);
				}else{
					console.error(err);
				}
			});
		},
		loadAttendance(){
			this.dates = [];
			this.students = [];
			axios.get('/api/sections/'+this.$route.params.id+'/attendance').then(response=>{
				this.dates = response.data.dates;
				this.students = response.data.students;
			}).catch(err=>{
				if(err.response){
					console.error(err.response.data.message);
				}else{
					console.error(err);
				}
			});
		},
		confirmDelete(date){

			this.$dialog.confirm('Confirm delete date:\"'+this.$moment(date.date).format('MMM DD, YYYY')+'\"?',{
				loader:true
			}).then((dialog)=>{
				this.deleteDate(date.id,dialog);
			});
		},
		deleteDate(section_attendance_id,dialog){

			axios.delete('/api/sections/'+this.$route.params.id+'/attendance/'+section_attendance_id).then(response=>{
				// console.log(response.data)
				this.loadAttendance();
				dialog.close()
			}).catch(err=>{
				dialog.close();
				let error = err.response.data;
				console.error(error);
				let message = error.message ? error.message : error;
				this.$dialog.alert('Error: ' + message);
			});
		},
		addAttendance(){
			this.new_date.error = null;
			let date = this.new_date.date;
			// console.log(this.new_date);
			if(date != null){
				date = this.$moment(date).format('YYYY-MM-DD');
			}
			console.log(date);
			axios.put('/api/sections/'+this.$route.params.id+'/attendance/',{
				date
			}).then(response=>{
				// console.log(response.data)
				this.loadAttendance();
			}).catch(err=>{
				if(err.response){
					console.error(err.response.data.message);
					this.new_date.error = err.response.data.errors['date'];
				}else{
					console.error(err);
				}
			});
		}
	}

}
</script>