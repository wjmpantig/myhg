<template>
	<div>
		<form @submit.prevent="addAttendance">
			<div class="field has-addons">
				<div class="control has-icons-left">
					<datepicker placeholder="New date" v-bind:input-class="{'input':true,'is-danger': new_date.errors.length>0}" v-model="new_date.date" format="MMM dd yyyy"></datepicker>
					<span class="icon is-left">
						<font-awesome-icon :icon="['far','calendar-alt']"></font-awesome-icon>
					</span>
				</div>
				
				<div class="control">
					<button type="submit" class="button is-primary is-outlined">Add date</button>
				</div>
			</div>
			<p class="help is-danger" v-show="new_date.errors.length > 0">
				{{new_date_error}}
			</p>
			
		</form>
		
		
		<div class="table-wrapper" v-show="dates.length > 0">
			<table v-bind:class="{'table is-hoverable is-bordered is-striped attendance-table': true,
			'is-fullwidth is-narrow':dates.length > 5}">				
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
					<tr v-for="(student,studentIndex) in students">
						<td>{{ student.last_name}}, {{ student.first_name}}</td>
						<td v-for="(date,index) in dates" 
							:class="{'has-text-centered' : true, 'has-background-danger' :false}" 
							>
							<input type="checkbox" v-model="student.attendance[date.id]"
								:ref="'check_' + student.id + '_' + date.id"
								v-on:change="togglePresent(student.id,date.id,student.attendance[date.id],index)"
								">
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
				errors: []
			}
		}
	},
	mounted(){
		this.loadAttendance();
	},
	computed:{
		new_date_error(){

			return this.new_date.errors == null ? '' : this.new_date.errors.join(' ');
		},
		// disabled_dates(){
		// 	foreach
		// }
	},
	methods:{
		togglePresent(student_id,section_attendance_id,value,index){
			if(value ==null){
				value = !this.students[index].attendance[section_attendance_id];
			}
			axios.post('/api/sections/'+this.$route.params.id+'/attendance',
				{section_attendance_id,student_id,value}
				).then(response=>{
				// console.log(response.data);
				const val = response.data.is_present;
				this.students[index].attendance[section_attendance_id] = val;
			}).catch(err=>{
				// this.students[index].attendance[section_attendance_id] = !value;
				if(err.response){
					console.error(err.response.data.message);
				}else{
					console.error(err);
				}
			});
		},
		key_test(){

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
			this.new_date.errors = [];
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
					this.new_date.errors = err.response.data.errors['date'];
				}else{
					console.error(err);
				}
			});
		}
	},
	updated(){
		this.$nextTick(function () {
		 
			$('.attendance-table').floatThead({
				responsiveContainer: function($table){
					return $table.closest('.table-wrapper');
				},
				autoReflow: true,
				position: 'fixed'
			})
		 
		})
	}

}
</script>