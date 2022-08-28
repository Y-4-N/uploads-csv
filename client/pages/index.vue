<template>
  <div class="container">
    <v-row justify="space-around">
      <v-col cols="auto">
        <v-dialog
            transition="dialog-top-transition"
            max-width="600"
        >
          <template v-slot:activator="{ on, attrs }">
            <v-btn color="primary" v-bind="attrs" v-on="on">
              Importar CSV
            </v-btn>
          </template>
          <template v-slot:default="dialog">
            <v-card>
              <v-toolbar
                  color="primary"
                  dark
              >Upload Files
              </v-toolbar>
              <v-card-text>
                <div class="large-12 mt-5 medium-12 small-12 filezone">
                  <input name="submit" type="file" id="files" ref="files" multiple v-on:change="handleFiles()"/>
                  <p>
                    Solte seus arquivos aqui <br>ou clique para procurar
                  </p>
                </div>

                <div v-for="(file, key) in files" class="file-listing">
                  <img class="preview" v-bind:ref="'preview'+parseInt(key)"/>
                  {{ file.name }}
                  <div class="success-container" v-if="file.id > 0">
                    Success
                  </div>
                  <div class="remove-container" v-else>
                    <a class="remove" v-on:click="removeFile(key)">Remove</a>
                  </div>
                </div>
                <div class="text-center">
                  <v-btn dark color="blue darken-2" @click="snackbar = true" class="submit-button mt-6"
                         v-on:click="submitFiles()" v-show="files.length > 0">
                    Enviar
                  </v-btn>
                  <v-snackbar color="green" v-model="snackbar" :multi-line="multiLine">
                    {{ text }}
                    <template v-slot:action="{ attrs }">
                      <v-btn color="white" text v-bind="attrs" @click="snackbar = false">
                        Fechar
                      </v-btn>
                    </template>
                  </v-snackbar>
                </div>
              </v-card-text>
              <v-card-actions class="justify-end">
                <v-btn
                    text
                    @click="dialog.value = false"
                >Close
                </v-btn>
              </v-card-actions>
            </v-card>
          </template>
        </v-dialog>
      </v-col>
    </v-row>
    <div id="chartdiv"/>
  </div>
</template>

<script>
import * as am4core from "@amcharts/amcharts4/core";
import * as am4charts from "@amcharts/amcharts4/charts";
import am4themes_animated from "@amcharts/amcharts4/themes/animated";

am4core.useTheme(am4themes_animated);
export default {
  data: () => ({
    multiLine: true,
    snackbar: false,
    files: [],
    text: `Arquivo adicionado com sucesso!!`,

    year: null,
    area: [],
    data: {
      area: [],
      year: [],
    },
  }),
  methods: {
    handleFiles() {
      let uploadedFiles = this.$refs.files.files;

      for (var i = 0; i < uploadedFiles.length; i++) {
        this.files.push(uploadedFiles[i]);
      }
      this.getImagePreviews();
    },
    getImagePreviews() {
      for (let i = 0; i < this.files.length; i++) {
        if (/\.(jpe?g|png|csv)$/i.test(this.files[i].name)) {
          let reader = new FileReader();
          reader.addEventListener("load", function () {
            this.$refs['preview' + parseInt(i)][0].src = reader.result;
          }.bind(this), false);
          reader.readAsDataURL(this.files[i]);
        } else {
          this.$nextTick(function () {
            this.$refs['preview' + parseInt(i)][0].src = '/generic.png';
          });
        }
      }
    },
    removeFile(key) {
      this.files.splice(key, 1);
      this.getImagePreviews();
    },
    submitFiles() {
      for (let i = 0; i < this.files.length; i++) {
        if (this.files[i].id) {
          continue;
        }
        let formData = new FormData();
        formData.append('file', this.files[i]);

        this.$axios.post('/' + 'upload-file',
            formData,
            {
              headers: {
                'Content-Type': 'multipart/form-data'
              }
            }
        ).then(function (data) {
          this.files[i].id = data['data']['id'];
          this.files.splice(i, 1, this.files[i]);
          console.log('success');
        }.bind(this)).catch(function (data) {
          console.log('error');
        });
      }
    }
  },
  async mounted() {
    console.log('Componente montado com sucesso.')
    let chart = am4core.create("chartdiv", am4charts.XYChart3D);

    chart.data = [{
      "area": "BPM Consulting",
      "year": 0.04
    }, {
      "area": "Audit",
      "year": 0.08
    }, {
      "area": "Corporate Finance",
      "year": 0.00
    }, {
      "area": "Consulting",
      "year": 0.04
    }, {
      "area": "Corporativo",
      "year": 0.00
    }, {
      "area": "Macro",
      "year": 0.0
    }, {
      "area": "Croma",
      "year": 0.24
    }];

    let categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
    categoryAxis.dataFields.category = "area";
    categoryAxis.renderer.grid.template.location = 0;
    categoryAxis.renderer.grid.template.strokeWidth = 0;
    categoryAxis.renderer.minGridDistance = 30;


    categoryAxis.renderer.labels.template.adapter.add("dy", function (dy, target) {
      if (target.dataItem && target.dataItem.index & 2 == 2) {
        return dy + 25;
      }
      return dy;
    });

    let valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
    valueAxis.renderer.grid.template.strokeWidth = 0
    valueAxis.renderer.labels.template.disabled = false
    valueAxis.cursorTooltipEnabled = false;

// Create series
    let series = chart.series.push(new am4charts.ColumnSeries());
    series.dataFields.valueY = "year";
    series.dataFields.categoryX = "area";
    series.name = "Years";
    series.columns.template.tooltipText = "{categoryX}: [bold]{valueY}[/]";
    series.columns.template.fillOpacity = .8;


    let columnTemplate = series.columns.template;
    columnTemplate.strokeWidth = 2;
    columnTemplate.strokeOpacity = 1;
  },
}
</script>

<style scoped>
#chartdiv {
  width: 100%;
  height: 500px;
}

input[type="file"] {
  opacity: 0;
  width: 100%;
  height: 200px;
  position: absolute;
  cursor: pointer;
}

.filezone {
  outline: 2px dashed grey;
  outline-offset: -10px;
  background: #ccc;
  color: dimgray;
  padding: 10px 10px;
  min-height: 200px;
  position: relative;
  cursor: pointer;
}

.filezone:hover {
  background: #c0c0c0;
}

.filezone p {
  font-size: 1.2em;
  text-align: center;
  padding: 50px 50px 50px 50px;
}

div.file-listing img {
  max-width: 90%;
}

div.file-listing {
  margin: auto;
  padding: 10px;
  border-bottom: 1px solid #ddd;
}

div.file-listing img {
  height: 100px;
}

div.success-container {
  text-align: center;
  color: green;
}

div.remove-container {
  text-align: center;
}

div.remove-container a {
  color: red;
  cursor: pointer;
}

a.submit-button {
  display: block;
  margin: auto;
  text-align: center;
  width: 200px;
  padding: 10px;
  text-transform: uppercase;
  background-color: #CCC;
  color: white;
  font-weight: bold;
  margin-top: 20px;
}
</style>
